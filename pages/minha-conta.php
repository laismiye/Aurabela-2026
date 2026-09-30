<?php
// Exibe todos os erros PHP para facilitar a depuração durante o desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Garante que o usuário esteja logado antes de acessar a página
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?form=login');
    exit;
}

include '../php/conexao.php';

// Padronização do nome da variável de conexão com o banco de dados
if (isset($conexao) && !isset($conn)) {
    $conn = $conexao;
}

$usuario_id = (int) $_SESSION['usuario_id'];
$mensagem_sucesso = "";
$mensagem_erro = "";

if (isset($_GET['status']) && $_GET['status'] === 'sucesso') {
    $mensagem_sucesso = "Seus dados foram atualizados com sucesso!";
}

try {
    $tem_telefone = false;
    $colunas = $conn->query("SHOW COLUMNS FROM usuarios LIKE 'telefone'");
    if ($colunas && $colunas->num_rows > 0) {
        $tem_telefone = true;
    }

    // Processamento do formulário quando enviado via POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $novo_nome = trim($_POST['nome'] ?? '');
        $novo_telefone = trim($_POST['telefone'] ?? '');
        $digitos_telefone = preg_replace('/\D/', '', $novo_telefone);

        if ($novo_nome === '' || mb_strlen($novo_nome) < 3) {
            $mensagem_erro = "Informe seu nome completo (mínimo 3 caracteres).";
        } elseif (mb_strlen($novo_nome) > 100) {
            $mensagem_erro = "O nome pode ter no máximo 100 caracteres.";
        } elseif ($novo_telefone !== '' && (strlen($digitos_telefone) < 10 || strlen($digitos_telefone) > 11)) {
            $mensagem_erro = "Informe um telefone válido com DDD. Ex: (11) 99999-9999";
        } else {
            if ($tem_telefone) {
                $stmt_update = $conn->prepare("UPDATE usuarios SET nome = ?, telefone = ? WHERE id = ?");
                $stmt_update->bind_param("ssi", $novo_nome, $novo_telefone, $usuario_id);
            } else {
                $stmt_update = $conn->prepare("UPDATE usuarios SET nome = ? WHERE id = ?");
                $stmt_update->bind_param("si", $novo_nome, $usuario_id);
            }

            if ($stmt_update && $stmt_update->execute()) {
                $_SESSION['usuario_nome'] = $novo_nome;
                $stmt_update->close();
                header('Location: minha-conta.php?status=sucesso');
                exit;
            }
            $mensagem_erro = "Não foi possível salvar seus dados. Tente novamente.";
        }
    }

    // Busca as informações atualizadas do usuário logado para exibir nos campos
    $campos = $tem_telefone ? "nome, email, telefone" : "nome, email";
    $stmt_select = $conn->prepare("SELECT $campos FROM usuarios WHERE id = ?");
    $stmt_select->bind_param("i", $usuario_id);
    $stmt_select->execute();
    $resultado = $stmt_select->get_result();
    $usuario = $resultado->fetch_assoc();
    $stmt_select->close();
} catch (Throwable $e) {
    $usuario = null;
    $mensagem_erro = "Não foi possível carregar seus dados no momento. Tente novamente mais tarde.";
}

if (!$usuario) {
    if ($mensagem_erro === "") {
        session_destroy();
        header('Location: login.php?form=login');
        exit;
    }
    $usuario = ['nome' => '', 'email' => ''];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $mensagem_erro !== "") {
    $usuario['nome'] = $_POST['nome'] ?? $usuario['nome'];
    $usuario['telefone'] = $_POST['telefone'] ?? ($usuario['telefone'] ?? '');
}

$valor_nome = htmlspecialchars($usuario['nome'] ?? '');
$valor_email = htmlspecialchars($usuario['email'] ?? '');
$valor_telefone = htmlspecialchars($usuario['telefone'] ?? '');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta - Aura Bela</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css?v=3">
</head>
<body class="account-body">

    <div class="account-card">
        <h1 class="account-title">Meus Dados</h1>

        <!-- Feedback visual das ações (Sucesso ou Erro) -->
        <?php if ($mensagem_sucesso): ?>
            <p class="feedback-msg msg-sucesso"><?= $mensagem_sucesso ?></p>
        <?php endif; ?>
        <?php if ($mensagem_erro): ?>
            <p class="feedback-msg msg-erro"><?= $mensagem_erro ?></p>
        <?php endif; ?>

        <!-- Formulário para edição dos dados cadastrais -->
        <form method="POST" action="minha-conta.php">
            <div class="input-group">
                <label for="conta-nome">Nome Completo</label>
                <input type="text" id="conta-nome" name="nome" value="<?= $valor_nome ?>" minlength="3" maxlength="100" required>
            </div>

            <div class="input-group">
                <label for="conta-email">E-mail (Login)</label>
                <!-- O e-mail permanece desabilitado para impedir alterações de credencial nesta tela -->
                <input type="email" id="conta-email" value="<?= $valor_email ?>" disabled style="background-color: #f5f5f5; color: #888; cursor: not-allowed;">
            </div>

            <?php if ($tem_telefone ?? false): ?>
            <div class="input-group">
                <label for="conta-telefone">Telefone / WhatsApp</label>
                <input type="tel" id="conta-telefone" name="telefone" class="js-phone-mask" value="<?= $valor_telefone ?>" placeholder="(00) 00000-0000" maxlength="15">
            </div>
            <?php endif; ?>

            <button type="submit" class="btn-save">Salvar Alterações</button>
        </form>

        <div class="back-link">
            <a href="../index.php">Voltar para o Início</a>
        </div>
    </div>

    <script src="../js/main.js?v=<?= filemtime(__DIR__ . '/../js/main.js') ?>"></script>
</body>
</html>