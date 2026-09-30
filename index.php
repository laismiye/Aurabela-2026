<?php
// Inicia ou retoma a sessão ativa para verificar se o usuário está logado
session_start();

// Define variáveis auxiliares para exibir personalizações da conta do usuário no menu
$usuario_logado = isset($_SESSION['usuario_id']);
$usuario_nome = $usuario_logado ? $_SESSION['usuario_nome'] : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura Bela</title>

    <!-- Versionamento do arquivo CSS via filemtime para evitar problemas com cache no navegador -->
    <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime(__DIR__ . '/css/style.css'); ?>">
    
    <!-- Importação de fontes externas via Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Menu de Navegação Principal -->
    <header class="navbar">
        <div class="logo-text">Aura Bela</div>
        <nav class="nav-links">
            <!-- Classe 'active' indicando a página atual -->
            <a href="index.php" class="active">Início</a>
            <a href="pages/quem-somos.php">Quem somos</a>
            <a href="pages/reiki.php">Reiki</a>
            <a href="pages/servicos.php">Serviços</a>
            
            <!-- Área do usuário no menu -->
            <div class="profile-container">
                <?php if ($usuario_logado): ?>
                    <span class="profile-username">Olá! <?php echo htmlspecialchars($usuario_nome); ?></span>
                <?php endif; ?>
                
                <!-- Ícone de perfil com link condicional (Redireciona ao login se visitante) -->
                <a href="<?php echo $usuario_logado ? '#' : 'pages/login.php?form=login'; ?>" class="profile-icon" id="profileDropdownBtn" title="<?php echo $usuario_logado ? 'Minha Conta' : 'Login / Cadastro'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </a>

                <!-- Submenu suspenso (Dropdown) exibido apenas quando logado -->
                <?php if ($usuario_logado): ?>
                    <div class="profile-dropdown" id="profileDropdownMenu">
                        <a href="pages/minha-conta.php">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            Minha Conta
                        </a>
                        <a href="pages/meus-agendamentos.php">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                            Agendamentos
                        </a>
                        <hr>
                        <a href="php/logout.php" style="color: #c92a2a !important;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                            Sair
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main>
        <!-- Banner Principal (Hero) -->
        <section class="hero-section">
            <img src="img/banner.png" alt="Aura Bela Tratamento Estético" class="hero-image">
        </section>

        <div class="content-wrapper">
            <!-- Seção de Apresentação -->
            <section class="about-section">
                <div class="about-logo">
                    <img src="img/logo.png" alt="Logo Aura Bela">
                </div>
                <div class="about-content">
                    <h2>Quem somos</h2>
                    <p>Nossa clínica não preza somente pela sua beleza exterior, mas também pela interior, cuidando tanto do seu corpo quanto da sua energia.</p>
                    <a href="pages/quem-somos.php" class="btn-primary">Ver mais</a>
                </div>
            </section>

            <!-- Seção de Destaque dos Serviços -->
            <section class="services-section">
                <div class="section-title">
                    <h2>Nossos Trabalhos</h2>
                    <p>A satisfação dos nossos clientes é nossa prioridade, oferecendo uma experiência única com excelência, cuidado e responsabilidade.</p>
                </div>

                <div class="services-grid">
                    <div class="service-card">
                        <div class="card-img-wrapper">
                            <img src="img/reiki.png" alt="Reiki">
                        </div>
                        <h3>Reiki</h3>
                        <p>Equilíbrio e bem-estar para corpo e mente.</p>
                    </div>

                    <div class="service-card">
                        <div class="card-img-wrapper">
                            <img src="img/harmonizacao.png" alt="Harmonização Facial">
                        </div>
                        <h3>Harmonização Facial</h3>
                        <p>Realce sua beleza natural com mais harmonia.</p>
                    </div>

                    <div class="service-card">
                        <div class="card-img-wrapper">
                            <img src="img/cilios.png" alt="Extensão de cílios">
                        </div>
                        <h3>Extensão de cílios</h3>
                        <p>Destaque seu olhar com mais charme e elegância.</p>
                    </div>
                </div>

                <div class="see-more-container">
                    <a href="pages/servicos.php" class="link-see-more">Ver mais</a>
                </div>
            </section>
        </div>
    </main>

    <!-- Rodapé Principal -->
    <footer>
        <div class="footer-top">
            <div class="footer-info">
                <h3>Horário de Funcionamento</h3>
                <p>Segunda à sexta | 08:00 às 20:00</p>
                <p>Sábado | 08:00 às 15:00</p>
            </div>
            
            <!-- Formulário da Newsletter conectado via AJAX -->
            <div class="footer-newsletter">
                <h3>Receba nossas novidades!</h3>
                <p>Cadastre seu e-mail e fique por dentro de todas as novidades Aura Bela.</p>
                <form class="newsletter-form" id="newsletter-form">
                    <input type="email" name="email" placeholder="Ex: Maria@gmail.com" required>
                    <button type="submit" id="btn-inscrever-newsletter" class="btn-newsletter">Inscrever</button>
                </form>
                <p class="newsletter-feedback"></p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <nav class="footer-links">
                <a href="index.php">Inicio</a>
                <a href="pages/quem-somos.php">Quem somos</a>
                <a href="pages/reiki.php">Reiki</a>
                <a href="pages/servicos.php">Serviços</a>
            </nav>
            <p class="copyright">&copy; 2026 Aura Bela. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- Botão Flutuante de Atendimento via WhatsApp -->
    <a href="https://wa.me/5511999999999?text=Ol%C3%A1%2C%20gostaria%20de%20agendar%20um%20hor%C3%A1rio%20na%20Aura%20Bela!"
       class="whatsapp-float"
       target="_blank"
       rel="noopener noreferrer"
       aria-label="Fale conosco pelo WhatsApp"
       title="Fale conosco pelo WhatsApp">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="38" height="38" fill="currentColor">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
            <path d="M12.001 2C6.478 2 2 6.478 2 12c0 1.876.507 3.633 1.393 5.146L2 22l4.976-1.363A9.955 9.955 0 0 0 12.001 22C17.523 22 22 17.523 22 12S17.523 2 12.001 2zm0 18.2a8.174 8.174 0 0 1-4.393-1.28l-.315-.187-2.953.809.79-2.877-.205-.297A8.175 8.175 0 0 1 3.8 12c0-4.53 3.671-8.2 8.201-8.2 4.529 0 8.199 3.67 8.199 8.2 0 4.53-3.67 8.2-8.199 8.2z"/>
        </svg>
    </a>

    <!-- Script com lógica interativa e envio de newsletter -->
    <script src="js/main.js"></script>
</body>
</html>