<?php
session_start();
$usuario_logado = isset($_SESSION['usuario_id']);
$usuario_nome = $usuario_logado ? $_SESSION['usuario_nome'] : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura Bela - Quem Somos</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="navbar">
        <div class="logo-text">Aura Bela</div>
        <nav class="nav-links">
            <a href="../index.php">Início</a>
            <a href="quem-somos.php" class="active">Quem somos</a>
            <a href="reiki.php">Reiki</a>
            <a href="servicos.php">Serviços</a>

            <div class="profile-container">
                <?php if ($usuario_logado): ?>
                    <span class="profile-username">Olá! <?php echo htmlspecialchars($usuario_nome); ?></span>
                <?php endif; ?>
                <a href="<?php echo $usuario_logado ? '#' : 'login.php?form=login'; ?>" class="profile-icon" id="profileDropdownBtn" title="<?php echo $usuario_logado ? 'Minha Conta' : 'Login / Cadastro'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </a>

                <?php if ($usuario_logado): ?>
                    <div class="profile-dropdown" id="profileDropdownMenu">
                        <a href="minha-conta.php">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            Minha Conta
                        </a>
                        <a href="meus-agendamentos.php">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            Agendamentos
                        </a>
                        <hr>
                        <a href="../php/logout.php" style="color: #c92a2a !important;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                            Sair
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main class="page-about">
        <section class="about-header">
            <h1>Quem somos</h1>
            <p class="subtitle">Conheça a essência que inspira cuidado, beleza e confiança.</p>
        </section>
        <section class="about-main-block">
            <div class="about-img-container">
                <img src="../img/Rectangle 8.png" alt="Tratamento Aura Bela" class="about-page-img">
            </div>
            <div class="about-text-container">
                <p>
                    A Aura Bela nasceu com o propósito de valorizar a beleza, a autoestima e o bem-estar, unindo estética, medicina
                    integrativa e práticas de Reiki para proporcionar cuidado, equilíbrio e experiências transformadoras.
                    Acreditamos que a verdadeira beleza está na autenticidade, por isso trabalhamos com dedicação, inovação
                    e atendimento humanizado para promover harmonia, confiança e qualidade de vida.
                </p>
            </div>
        </section>
    </main>
    <footer class="footer-simple">
        <div class="footer-bottom">
            <nav class="footer-links">
                <a href="../index.php">Inicio</a>
                <a href="../pages/quem-somos.php">Quem somos</a>
                <a href="../pages/reiki.php">Reiki</a>
                <a href="../pages/servicos.php">Serviços</a>
            </nav>
            <p class="copyright">&copy; 2026 Aura Bela. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>
</html>
<script src="../js/main.js"></script>