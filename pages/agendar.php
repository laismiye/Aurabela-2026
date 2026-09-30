<?php
// Configurações de exibição de erros (útil durante o desenvolvimento)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Função auxiliar para exibir blocos de erro formatados e encerrar a execução
function pararComErro($mensagem) {
    echo "<div style='padding:20px; background:#FFE8E8; color:#A71D1D; border:1px solid #A71D1D; font-family:sans-serif; border-radius:8px; max-width:600px; margin:20px auto;'>";
    echo "<h3>⚠️ Erro Identificado no Agendamento:</h3>";
    echo "<p>$mensagem</p>";
    echo "</div>";
    exit;
}

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Localiza dinamicamente o arquivo de conexão dependendo do diretório pai
    if (file_exists('php/conexao.php')) {
        $caminho_conexao = 'php/conexao.php'; 
        $prefixo_pasta = '';
    } elseif (file_exists('../php/conexao.php')) {
        $caminho_conexao = '../php/conexao.php'; 
        $prefixo_pasta = '../';
    } else {
        pararComErro("O arquivo de conexão <code>conexao.php</code> não foi localizado na pasta <code>php/</code>. Verifique a estrutura das suas pastas.");
    }
    
    require_once $caminho_conexao;

    if (!isset($conn) || !$conn) {
        pararComErro("A variável de conexão <code>\$conn</code> não foi iniciada dentro do seu arquivo conexao.php.");
    }

    // Validação de autenticação: obriga o usuário a estar logado
    if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['user_id']) && !isset($_SESSION['id'])) {
        $url_login = $prefixo_pasta . 'pages/login.php?form=login&erro=necessario_login';
        echo "<script>
            alert('Para agendar, você precisa estar logado! Redirecionando para a página de login...');
            window.location.href = '$url_login';
        </script>";
        exit;
    }

    // Identifica a chave de sessão correspondente ao ID do usuário
    $id_usuario = null;
    if (isset($_SESSION['usuario_id'])) $id_usuario = $_SESSION['usuario_id'];
    elseif (isset($_SESSION['user_id'])) $id_usuario = $_SESSION['user_id'];
    elseif (isset($_SESSION['id'])) $id_usuario = $_SESSION['id'];

    $nome_padrao = "";
    $sobrenome_padrao = "";

    // Busca os dados do usuário para pré-preencher os campos de nome e sobrenome
    if ($id_usuario) {
        $busca_cliente = $conn->prepare("SELECT nome FROM usuarios WHERE id = ?");
        if ($busca_cliente) {
            $busca_cliente->bind_param("i", $id_usuario);
            $busca_cliente->execute();
            $resultado = $busca_cliente->get_result();
            
            if ($dados_cliente = $resultado->fetch_assoc()) {
                // Divide o nome completo em primeiro nome e sobrenome
                $nome_completo = explode(" ", $dados_cliente['nome']);
                $nome_padrao = $nome_completo[0];
                $sobrenome_padrao = isset($nome_completo[1]) ? implode(" ", array_slice($nome_completo, 1)) : '';
            }
            $busca_cliente->close();
        } else {
            pararComErro("Falha ao consultar a tabela 'usuarios': " . $conn->error);
        }
    }

    // Captura o serviço selecionado e a data desejada (padrão: hoje)
    $servico_selecionado = isset($_GET['servico']) ? htmlspecialchars($_GET['servico']) : 'Serviço Geral';
    $data_escolhida = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');

    // Grade de horários padrão do estabelecimento
    $horarios_padrao = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00', '17:00'];

    // Busca agendamentos já existentes na data para desabilitar horários ocupados
    $ocupados = [];
    $stmt = $conn->prepare("SELECT horario_agendamento FROM agendamentos WHERE data_agendamento = ?");
    
    if ($stmt) {
        $stmt->bind_param("s", $data_escolhida);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $ocupados[] = date('H:i', strtotime($row['horario_agendamento']));
        }
        $stmt->close();
    } else {
        pararComErro("Erro crítico na tabela <b>agendamentos</b>: " . $conn->error . "<br><br>Verifique se as colunas <code>data_agendamento</code> e <code>horario_agendamento</code> existem.");
    }

} catch (Exception $e) {
    pararComErro("Exceção capturada: " . $e->getMessage());
} catch (Error $err) {
    pararComErro("Erro fatal do PHP: " . $err->getMessage() . " na linha " . $err->getLine());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura Bela - Agendamento</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $prefixo_pasta ?>css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>">
</head>
<body>

    <!-- Cabeçalho de navegação principal -->
    <header class="navbar">
        <div class="logo-text">Aura Bela</div>
        <nav class="nav-links">
            <a href="<?= $prefixo_pasta ?>index.php">Início</a>
            <a href="<?= $prefixo_pasta ?>pages/quem-somos.php">Quem somos</a>
            <a href="<?= $prefixo_pasta ?>pages/reiki.php">Reiki</a>
            <a href="<?= $prefixo_pasta ?>pages/servicos.php">Serviços</a>
            <a href="<?= $prefixo_pasta ?>pages/login.php?form=login" class="profile-icon" title="Login / Cadastro">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </a>
        </nav>
    </header>

    <main class="booking-section">
        <div class="schedule-container">
            <h1 class="auth-title">Agende seu Horário</h1>
            <p class="required-note">Campos marcados com <span class="required-mark">*</span> são obrigatórios.</p>

            <!-- Form GET: Filtro por data (recarrega a página ao mudar o campo) -->
            <form method="GET" id="dateForm" style="margin-bottom: 25px;">
                <input type="hidden" name="servico" value="<?= $servico_selecionado ?>">
                <div class="input-group">
                    <label for="agendamento-data">1. Escolha o Dia do Atendimento: <span class="required-mark" aria-hidden="true">*</span></label>
                    <input type="date" id="agendamento-data" name="data" value="<?= htmlspecialchars($data_escolhida) ?>" min="<?= date('Y-m-d') ?>" required onchange="if (this.value) document.getElementById('dateForm').submit();">
                </div>
            </form>

            <!-- Form POST: Envio final dos dados do agendamento -->
            <form action="<?= $prefixo_pasta ?>php/processa_agendamento.php" method="POST" id="bookingForm" novalidate>
                <input type="hidden" name="servico" value="<?= $servico_selecionado ?>">
                <input type="hidden" name="data" value="<?= htmlspecialchars($data_escolhida) ?>">

                <div class="input-group">
                    <label>Procedimento Escolhido:</label>
                    <input type="text" value="<?= $servico_selecionado ?>" disabled style="background:#f0f0f0; color: #666; font-weight: 600; border-color: #ddd;">
                </div>

                <div class="input-group">
                    <label for="agendamento-nome">Nome: <span class="required-mark" aria-hidden="true">*</span></label>
                    <input type="text" id="agendamento-nome" name="nome" value="<?= htmlspecialchars($nome_padrao) ?>" required aria-required="true">
                    <span class="field-error" data-error-for="agendamento-nome">Informe seu nome.</span>
                </div>

                <div class="input-group">
                    <label for="agendamento-sobrenome">Sobrenome: <span class="required-mark" aria-hidden="true">*</span></label>
                    <input type="text" id="agendamento-sobrenome" name="sobrenome" value="<?= htmlspecialchars($sobrenome_padrao) ?>" required aria-required="true">
                    <span class="field-error" data-error-for="agendamento-sobrenome">Informe seu sobrenome.</span>
                </div>

                <div class="input-group">
                    <label for="agendamento-telefone">Telefone de Contato: <span class="required-mark" aria-hidden="true">*</span></label>
                    <input type="tel" id="agendamento-telefone" name="telefone" class="js-phone-mask" placeholder="Ex: (11) 99999-9999" maxlength="15" required aria-required="true">
                    <span class="field-error" data-error-for="agendamento-telefone">Informe um telefone válido com DDD.</span>
                </div>

                <!-- Seleção de horários dinâmicos baseados nos horários já ocupados no banco -->
                <div class="input-group">
                    <label>2. Escolha o Horário Disponível: <span class="required-mark" aria-hidden="true">*</span></label>
                    <span class="field-error" data-error-for="horario">Selecione um horário disponível.</span>
                    <div class="time-grid" id="timeGrid">
                        <?php foreach ($horarios_padrao as $hr): 
                            $esta_ocupado = in_array($hr, $ocupados);
                        ?>
                            <input type="radio" name="horario" value="<?= $hr ?>" id="time-<?= $hr ?>" class="time-radio" <?= $esta_ocupado ? 'disabled' : '' ?> required>
                            <label for="time-<?= $hr ?>" class="time-label">
                                <?= $hr ?> <?= $esta_ocupado ? '(Ocupado)' : '' ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="btn-auth-submit">Confirmar Agendamento</button>
            </form>
        </div>
    </main>

    <!-- Rodapé global com formulário de inscrição na newsletter -->
    <footer>
        <div class="footer-top">
            <div class="footer-info">
                <h3>Horário de Funcionamento</h3>
                <p>Segunda à sexta | 08:00 às 20:00</p>
                <p>Sábado | 08:00 às 15:00</p>
            </div>
            <div class="footer-newsletter">
                <h3>Receba nossas novidades!</h3>
                <p>Cadastre seu e-mail e fique por dentro de todas as novidades Aura Bela.</p>
                <form class="newsletter-form" action="<?= $prefixo_pasta ?>php/newsletter.php" method="POST">
                    <input type="email" name="email" placeholder="Ex: Maria@gmail.com" required>
                    <button type="submit" class="btn-newsletter">Inscrever</button>
                </form>
            </div>
        </div>
        
        <div class="footer-bottom">
            <nav class="footer-links">
                <a href="<?= $prefixo_pasta ?>index.php">Inicio</a>
                <a href="<?= $prefixo_pasta ?>pages/quem-somos.php">Quem somos</a>
                <a href="<?= $prefixo_pasta ?>pages/reiki.php">Reiki</a>
                <a href="<?= $prefixo_pasta ?>pages/servicos.php">Serviços</a>
            </nav>
            <p class="copyright">&copy; 2026 Aura Bela. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="<?= $prefixo_pasta ?>js/main.js?v=<?= filemtime(__DIR__ . '/../js/main.js') ?>"></script>
</body>
</html>