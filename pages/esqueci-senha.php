<?php
session_start();

if (isset($_GET['reiniciar'])) {
    unset($_SESSION['recuperacao']);
    header('Location: esqueci-senha.php');
    exit;
}

if (!isset($_SESSION['recuperacao'])) {
    $_SESSION['recuperacao'] = ['etapa' => 'email'];
}

$recuperacao = &$_SESSION['recuperacao'];
$erro = '';
$aviso = '';

function gerarCodigo(array &$recuperacao) {
    $recuperacao['codigo'] = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $recuperacao['expira_em'] = time() + 600;
    $recuperacao['tentativas'] = 0;
}

function mascararEmail($email) {
    [$usuario, $dominio] = explode('@', $email, 2);
    $visivel = mb_substr($usuario, 0, 2);
    return $visivel . str_repeat('*', max(3, mb_strlen($usuario) - 2)) . '@' . $dominio;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'enviar_email') {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Informe um e-mail válido.';
        } else {
            $recuperacao['email'] = $email;
            $recuperacao['etapa'] = 'codigo';
            gerarCodigo($recuperacao);
        }
    } elseif ($acao === 'reenviar' && $recuperacao['etapa'] === 'codigo') {
        gerarCodigo($recuperacao);
        $aviso = 'Um novo código foi enviado.';
    } elseif ($acao === 'verificar_codigo' && $recuperacao['etapa'] === 'codigo') {
        $codigo = preg_replace('/\D/', '', $_POST['codigo'] ?? '');
        $recuperacao['tentativas']++;

        if (time() > $recuperacao['expira_em']) {
            $erro = 'Este código expirou. Clique em "Reenviar código".';
        } elseif ($recuperacao['tentativas'] > 5) {
            $erro = 'Muitas tentativas. Clique em "Reenviar código" para gerar um novo.';
        } elseif ($codigo !== $recuperacao['codigo']) {
            $erro = 'Código inválido. Confira e tente novamente.';
        } else {
            $recuperacao['etapa'] = 'senha';
        }
    } elseif ($acao === 'nova_senha' && $recuperacao['etapa'] === 'senha') {
        $senha = $_POST['senha'] ?? '';
        $confirmar = $_POST['confirmar_senha'] ?? '';

        if (strlen($senha) < 6) {
            $erro = 'A senha deve ter pelo menos 6 caracteres.';
        } elseif ($senha !== $confirmar) {
            $erro = 'As senhas não coincidem.';
        } else {
            unset($_SESSION['recuperacao']);
            header('Location: login.php?form=login&senha=redefinida');
            exit;
        }
    }
}

$etapa = $recuperacao['etapa'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura Bela - Recuperar Senha</title>
    <link rel="stylesheet" href="../css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="auth-body">
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

            <?php if ($etapa === 'email'): ?>
                <p class="auth-subtitle">Informe o e-mail da sua conta e enviaremos um código para você redefinir sua senha.</p>

                <?php if ($erro): ?>
                    <p class="auth-error"><?= htmlspecialchars($erro) ?></p>
                <?php endif; ?>

                <form class="auth-form" method="POST" action="esqueci-senha.php">
                    <input type="hidden" name="acao" value="enviar_email">
                    <div class="input-group">
                        <label for="recuperar-email">Email</label>
                        <input type="email" id="recuperar-email" name="email" placeholder="Ex: Maria@gmail.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                    </div>
                    <button type="submit" class="btn-auth-submit">Enviar código</button>
                </form>

            <?php elseif ($etapa === 'codigo'): ?>
                <p class="auth-subtitle">Enviamos um código de 6 dígitos para <strong><?= htmlspecialchars(mascararEmail($recuperacao['email'])) ?></strong>. Digite-o abaixo.</p>

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
                        <input type="text" id="recuperar-codigo" name="codigo" placeholder="000000" inputmode="numeric" pattern="\d{6}" maxlength="6" autocomplete="one-time-code" required autofocus>
                    </div>
                    <button type="submit" class="btn-auth-submit">Verificar código</button>
                </form>

                <form method="POST" action="esqueci-senha.php" class="forgot-password-wrapper" style="margin-top: 12px;">
                    <input type="hidden" name="acao" value="reenviar">
                    <button type="submit" class="forgot-link" style="background: none; border: none; cursor: pointer; font-family: inherit; padding: 0;">Não recebeu? Reenviar código</button>
                </form>

            <?php else: ?>
                <p class="auth-subtitle">Código confirmado! Agora crie uma nova senha para sua conta.</p>

                <?php if ($erro): ?>
                    <p class="auth-error"><?= htmlspecialchars($erro) ?></p>
                <?php endif; ?>

                <form class="auth-form" method="POST" action="esqueci-senha.php">
                    <input type="hidden" name="acao" value="nova_senha">
                    <div class="input-group password-group">
                        <label for="nova-senha">Nova senha</label>
                        <div class="input-with-icon">
                            <input type="password" id="nova-senha" name="senha" placeholder="Mínimo de 6 caracteres" minlength="6" required autofocus>
                            <button type="button" class="toggle-password" onclick="togglePasswordVisibility('nova-senha', this)">
                                <i class="fa-regular fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>
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

            <div class="auth-switch">
                <?php if ($etapa !== 'email'): ?>
                    <p><a href="esqueci-senha.php?reiniciar=1">Usar outro e-mail</a></p>
                <?php endif; ?>
                <p>Lembrou a senha? <a href="login.php?form=login">Fazer Login</a></p>
            </div>
        </div>
    </main>

    <footer class="auth-footer">
        <p class="copyright">&copy; 2026 Aura Bela. Todos os direitos reservados.</p>
    </footer>

    <script src="../js/main.js"></script>
</body>
</html>
