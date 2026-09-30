<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura Bela - Autenticação</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="auth-body">
    <!-- Topo da página com navegação simples -->
    <header class="navbar auth-navbar">
        <div class="logo-text">Aura Bela</div>
        <a href="?form=login" class="profile-icon" title="Ir para o login">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </a>
    </header>

    <main class="auth-container">

        <!-- Card 1: Formulário de Login -->
        <div class="auth-card" id="login-card">
            <div class="auth-logo-wrapper">
                <img src="../img/logo.png" alt="Aura Bela Logo" class="auth-min-logo">
            </div>
            <h1 class="auth-title">Login</h1>

            <!-- Alerta em caso de erro na tentativa de login -->
            <?php if (isset($_GET['erro'])): ?>
                <p class="auth-error">E-mail ou senha incorretos.</p>
            <?php endif; ?>

            <?php if (isset($_GET['senha']) && $_GET['senha'] === 'redefinida'): ?>
                <p class="auth-success">Senha redefinida com sucesso! Faça login.</p>
            <?php endif; ?>

            <form class="auth-form" action="../php/login.php" method="POST">
                <div class="input-group">
                    <label for="login-email">Email</label>
                    <input type="email" id="login-email" name="email" placeholder="Ex: Maria@gmail.com" required>
                </div>
                <div class="input-group password-group">
                    <label for="login-password">Senha</label>
                    <div class="input-with-icon">
                        <input type="password" id="login-password" name="senha" placeholder="Digite sua senha" required>
                        <!-- Botão que aciona a função JavaScript para revelar/ocultar a senha -->
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('login-password', this)">
                            <i class="fa-regular fa-eye-slash"></i>
                        </button>
                    </div>
                </div>
                <div class="forgot-password-wrapper">
                    <a href="esqueci-senha.php" class="forgot-link">Esqueci minha senha</a>
                </div>
                <button type="submit" class="btn-auth-submit">Entrar</button>
            </form>

            <!-- Alternância de tela: oculta login e exibe cadastro via JS -->
            <div class="auth-switch">
                <p>Não possui conta? <a href="#" onclick="switchForm('cadastro')">Criar conta</a></p>
            </div>
        </div>

        <!-- Card 2: Formulário de Cadastro (Inicia oculto pela classe d-none) -->
        <div class="auth-card d-none" id="cadastro-card">
            <div class="auth-logo-wrapper">
                <img src="../img/logo.png" alt="Aura Bela Logo" class="auth-min-logo">
            </div>
            <h1 class="auth-title">Cadastro</h1>

            <!-- Mensagem de confirmação após cadastro realizado -->
            <?php if (isset($_GET['cadastro']) && $_GET['cadastro'] === 'sucesso'): ?>
                <p class="auth-success">Cadastro realizado com sucesso! Faça login.</p>
            <?php endif; ?>

            <form class="auth-form" action="../php/cadastro.php" method="POST">
                <div class="input-group">
                    <label for="register-name">Nome</label>
                    <input type="text" id="register-name" name="nome" placeholder="Ex: Maria Perez" required>
                </div>
                <div class="input-group">
                    <label for="register-email">Email</label>
                    <input type="email" id="register-email" name="email" placeholder="Ex: Maria@gmail.com" required>
                </div>
                <div class="input-group">
                    <label for="register-password">Senha</label>
                    <input type="password" id="register-password" name="senha" placeholder="Digite sua senha" required>
                </div>
                <div class="input-group password-group">
                    <label for="register-confirm-password">Confirmar senha</label>
                    <div class="input-with-icon">
                        <input type="password" id="register-confirm-password" name="confirmar_senha" placeholder="Confirme sua senha" required>
                        <!-- Botão para revelar/ocultar senha no campo de confirmação -->
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility('register-confirm-password', this)">
                            <i class="fa-regular fa-eye-slash"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn-auth-submit">Cadastrar</button>
            </form>

            <!-- Alternância de tela: oculta cadastro e exibe login via JS -->
            <div class="auth-switch">
                <p>Já possui uma conta? <a href="#" onclick="switchForm('login')">Fazer Login</a></p>
            </div>
        </div>

    </main>

    <footer class="auth-footer">
        <p class="copyright">&copy; 2026 Aura Bela. Todos os direitos reservados.</p>
    </footer>

</body>
</html>
<!-- Script que controla as funções switchForm() e togglePasswordVisibility() -->
<script src="../js/main.js"></script>