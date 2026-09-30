<?php
// Inicia a sessão para guardar o estado da recuperação de senha entre as requisições
session_start();

// Se o usuário clicou em "Usar outro e-mail", descarta o processo atual e recomeça
if (isset($_GET['reiniciar'])) {
    unset($_SESSION['recuperacao']);
    header('Location: esqueci-senha.php');
    exit;
}

// Primeira visita: define a etapa inicial do fluxo (pedir o e-mail)
if (!isset($_SESSION['recuperacao'])) {
    $_SESSION['recuperacao'] = ['etapa' => 'email'];
}

// Referência (&) à sessão: qualquer alteração em $recuperacao já é salva na sessão
$recuperacao = &$_SESSION['recuperacao'];
$erro = '';   // Mensagem de erro exibida na tela
$aviso = '';  // Mensagem de sucesso/aviso exibida na tela

// Gera um novo código de 6 dígitos e reinicia validade e tentativas
function gerarCodigo(array &$recuperacao) {
    // random_int é criptograficamente seguro; str_pad completa com zeros à esquerda (ex: 004821)
    $recuperacao['codigo'] = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $recuperacao['expira_em'] = time() + 600; // Válido por 10 minutos (600 segundos)
    $recuperacao['tentativas'] = 0;           // Zera o contador de tentativas erradas
}

// Esconde parte do e-mail para exibir na tela (ex: ma***@gmail.com)
function mascararEmail($email) {
    [$usuario, $dominio] = explode('@', $email, 2);   // Separa o que vem antes e depois do @
    $visivel = mb_substr($usuario, 0, 2);             // Mantém apenas os 2 primeiros caracteres
    // Troca o restante por asteriscos (no mínimo 3)
    return $visivel . str_repeat('*', max(3, mb_strlen($usuario) - 2)) . '@' . $dominio;
}

// Processa os formulários enviados (todos usam POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // O campo oculto "acao" indica qual formulário foi enviado
    $acao = $_POST['acao'] ?? '';

    // ETAPA 1: usuário informou o e-mail
    if ($acao === 'enviar_email') {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Informe um e-mail válido.';
        } else {
            // Guarda o e-mail, avança para a etapa do código e gera o código
            $recuperacao['email'] = $email;
            $recuperacao['etapa'] = 'codigo';
            gerarCodigo($recuperacao);
        }
    // Reenvio do código (só permitido na etapa do código)
    } elseif ($acao === 'reenviar' && $recuperacao['etapa'] === 'codigo') {
        gerarCodigo($recuperacao);
        $aviso = 'Um novo código foi enviado.';
    // ETAPA 2: usuário digitou o código recebido
    } elseif ($acao === 'verificar_codigo' && $recuperacao['etapa'] === 'codigo') {
        // Remove qualquer caractere que não seja número
        $codigo = preg_replace('/\D/', '', $_POST['codigo'] ?? '');
        $recuperacao['tentativas']++; // Conta esta tentativa

        if (time() > $recuperacao['expira_em']) {
            // Passou de 10 minutos
            $erro = 'Este código expirou. Clique em "Reenviar código".';
        } elseif ($recuperacao['tentativas'] > 5) {
            // Limite de 5 tentativas para evitar força bruta
            $erro = 'Muitas tentativas. Clique em "Reenviar código" para gerar um novo.';
        } elseif ($codigo !== $recuperacao['codigo']) {
            $erro = 'Código inválido. Confira e tente novamente.';
        } else {
            // Código correto: libera a etapa de criar nova senha
            $recuperacao['etapa'] = 'senha';
        }
    // ETAPA 3: usuário definiu a nova senha (só permitido após validar o código)
    } elseif ($acao === 'nova_senha' && $recuperacao['etapa'] === 'senha') {
        $senha = $_POST['senha'] ?? '';
        $confirmar = $_POST['confirmar_senha'] ?? '';

        if (strlen($senha) < 6) {
            $erro = 'A senha deve ter pelo menos 6 caracteres.';
        } elseif ($senha !== $confirmar) {
            $erro = 'As senhas não coincidem.';
        } else {
            // Finaliza o processo: limpa a sessão e volta ao login com aviso de sucesso
            unset($_SESSION['recuperacao']);
            header('Location: login.php?form=login&senha=redefinida');
            exit;
        }
    }
}

// Etapa atual ('email', 'codigo' ou 'senha'), usada abaixo para escolher o que exibir
$etapa = $recuperacao['etapa'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura Bela - Recuperar Senha</title>
    <!-- CSS principal; filemtime no ?v= evita cache quando o arquivo é alterado -->
    <link rel="stylesheet" href="../css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>">
    <!-- Fonte Poppins (Google Fonts) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Ícones Font Awesome (usados no botão de mostrar/ocultar senha) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="auth-body">
    <!-- Barra superior com o nome da clínica e atalho para o login -->
    <header class="navbar auth-navbar">
        <div class="logo-text">Aura Bela</div>
        <a href="login.php?form=login" class="profile-icon" title="Ir para o login">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </a>
    </header>

    <main class="auth-container">
        <div class="auth-card">
            <div class="auth-logo-wrapper">
                <img src="../img/logo.png" alt="Aura Bela Logo" class="auth-min-logo">
            </div>
            <h1 class="auth-title">Recuperar senha</h1>

            <!-- Mostra um bloco diferente dependendo da etapa atual -->
            <?php if ($etapa === 'email'): ?>
                <!-- ETAPA 1: pedir o e-mail -->
                <p class="auth-subtitle">Informe o e-mail da sua conta e enviaremos um código para você redefinir sua senha.</p>

                <?php if ($erro): ?>
                    <!-- htmlspecialchars protege contra XSS ao exibir texto na página -->
                    <p class="auth-error"><?= htmlspecialchars($erro) ?></p>
                <?php endif; ?>

                <form class="auth-form" method="POST" action="esqueci-senha.php">
                    <!-- Campo oculto que identifica qual formulário foi enviado -->
                    <input type="hidden" name="acao" value="enviar_email">
                    <div class="input-group">
                        <label for="recuperar-email">Email</label>
                        <!-- Mantém o e-mail digitado caso ocorra erro de validação -->
                        <input type="email" id="recuperar-email" name="email" placeholder="Ex: Maria@gmail.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                    </div>
                    <button type="submit" class="btn-auth-submit">Enviar código</button>
                </form>

            <?php elseif ($etapa === 'codigo'): ?>
                <!-- ETAPA 2: digitar o código de 6 dígitos -->
                <p class="auth-subtitle">Enviamos um código de 6 dígitos para <strong><?= htmlspecialchars(mascararEmail($recuperacao['email'])) ?></strong>. Digite-o abaixo.</p>

                <!-- Como não há envio real de e-mail, o código aparece na tela (modo demonstração) -->
                <p class="auth-success">Ambiente de demonstração — seu código é <strong><?= $recuperacao['codigo'] ?></strong></p>

                <?php if ($aviso): ?>
                    <p class="auth-success"><?= htmlspecialchars($aviso) ?></p>
                <?php endif; ?>
                <?php if ($erro): ?>
                    <p class="auth-error"><?= htmlspecialchars($erro) ?></p>
                <?php endif; ?>

                <form class="auth-form" method="POST" action="esqueci-senha.php">
                    <input type="hidden" name="acao" value="verificar_codigo">
                    <div class="input-group">
                        <label for="recuperar-codigo">Código de verificação</label>
                        <!-- Aceita só 6 números; teclado numérico no celular; autocomplete de código por SMS/e-mail -->
                        <input type="text" id="recuperar-codigo" name="codigo" placeholder="000000" inputmode="numeric" pattern="\d{6}" maxlength="6" autocomplete="one-time-code" required autofocus>
                    </div>
                    <button type="submit" class="btn-auth-submit">Verificar código</button>
                </form>

                <!-- Formulário separado para reenviar o código, com aparência de link -->
                <form method="POST" action="esqueci-senha.php" class="forgot-password-wrapper" style="margin-top: 12px;">
                    <input type="hidden" name="acao" value="reenviar">
                    <button type="submit" class="forgot-link" style="background: none; border: none; cursor: pointer; font-family: inherit; padding: 0;">Não recebeu? Reenviar código</button>
                </form>

            <?php else: ?>
                <!-- ETAPA 3: criar a nova senha -->
                <p class="auth-subtitle">Código confirmado! Agora crie uma nova senha para sua conta.</p>

                <?php if ($erro): ?>
                    <p class="auth-error"><?= htmlspecialchars($erro) ?></p>
                <?php endif; ?>

                <form class="auth-form" method="POST" action="esqueci-senha.php">
                    <input type="hidden" name="acao" value="nova_senha">
                    <!-- Campo de nova senha com botão de mostrar/ocultar -->
                    <div class="input-group password-group">
                        <label for="nova-senha">Nova senha</label>
                        <div class="input-with-icon">
                            <input type="password" id="nova-senha" name="senha" placeholder="Mínimo de 6 caracteres" minlength="6" required autofocus>
                            <!-- togglePasswordVisibility está definida em ../js/main.js -->
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('nova-senha', this)">
                                <i class="fa-regular fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Campo de confirmação da senha -->
                    <div class="input-group password-group">
                        <label for="confirmar-nova-senha">Confirmar nova senha</label>
                        <div class="input-with-icon">
                            <input type="password" id="confirmar-nova-senha" name="confirmar_senha" placeholder="Confirme sua nova senha" minlength="6" required>
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirmar-nova-senha', this)">
                                <i class="fa-regular fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn-auth-submit">Redefinir senha</button>
                </form>
            <?php endif; ?>

            <!-- Links de navegação exibidos em todas as etapas -->
            <div class="auth-switch">
                <?php if ($etapa !== 'email'): ?>
                    <!-- Só aparece depois que o e-mail já foi informado -->
                    <p><a href="esqueci-senha.php?reiniciar=1">Usar outro e-mail</a></p>
                <?php endif; ?>
                <p>Lembrou a senha? <a href="login.php?form=login">Fazer Login</a></p>
            </div>
        </div>
    </main>

    <footer class="auth-footer">
        <p class="copyright">&copy; 2026 Aura Bela. Todos os direitos reservados.</p>
    </footer>

    <!-- Script principal do site (contém a função de mostrar/ocultar senha) -->
    <script src="../js/main.js"></script>
</body>
</html>