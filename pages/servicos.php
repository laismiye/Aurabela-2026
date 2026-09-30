<?php
// Inicia ou retoma a sessão ativa para verificar a autenticação do usuário
session_start();
$usuario_logado = isset($_SESSION['usuario_id']);
$usuario_nome = $usuario_logado ? $_SESSION['usuario_nome'] : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura Bela - Serviços</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Menu de Navegação Superior -->
    <header class="navbar">
        <div class="logo-text">Aura Bela</div>
        <nav class="nav-links">
            <a href="../index.php">Início</a>
            <a href="quem-somos.php">Quem somos</a>
            <a href="reiki.php">Reiki</a>
            <!-- Classe 'active' aplicada para destacar a página atual -->
            <a href="servicos.php" class="active">Serviços</a>

            <!-- Bloco do usuário e dropdown do perfil -->
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

    <!-- Conteúdo Principal de Serviços -->
    <main class="page-services">
    
        <section class="services-header">
            <h1>Serviços</h1>
            <p>Tratamentos desenvolvidos para realçar sua beleza natural e proporcionar mais qualidade de vida.</p>
        </section>

        <!-- Lista de Serviços (utiliza a classe 'reverse' alternadamente para criar o efeito zig-zag) -->
        <section class="services-list-container">
            
            <div class="service-row">
                <div class="service-info-block">
                    <h2>Preenchimento labial</h2>
                    <p>O preenchimento labial é um procedimento estético que realça a beleza natural dos lábios, trazendo mais volume, definição e hidratação, além de bem-estar.</p>
                    <span class="price">R$ 1.500,00</span>
                    <a href="agendar.php?servico=Preenchimento labial" class="btn-schedule">Agendar agora</a>
                </div>
                <div class="service-img-block">
                    <img src="../img/preenchimento-labial.png" alt="Preenchimento labial">
                </div>
            </div>

            <div class="service-row reverse">
                <div class="service-info-block">
                    <h2>Harmonização facial</h2>
                    <p>A harmonização facial busca sua beleza natural, trazendo equilíbrio, confiança e autoestima.</p>
                    <span class="price">R$ 3.000,00</span>
                    <a href="agendar.php?servico=Harmonização facial" class="btn-schedule">Agendar agora</a>
                </div>
                <div class="service-img-block">
                    <img src="../img/harmonizacao-facial.png" alt="Harmonização facial">
                </div>
            </div>

            <div class="service-row">
                <div class="service-info-block">
                    <h2>Botox</h2>
                    <p>Reduz marcas, linhas de expressão e ajuda a prevenir rugas, trazendo um aspecto jovem e natural.</p>
                    <span class="price">R$ 1.150,00</span>
                    <a href="agendar.php?servico=Botox" class="btn-schedule">Agendar agora</a>
                </div>
                <div class="service-img-block">
                    <img src="../img/botox.png" alt="Botox">
                </div>
            </div>

            <div class="service-row reverse">
                <div class="service-info-block">
                    <h2>Drenagem</h2>
                    <p>A drenagem linfática ajuda a reduzir o inchaço, a melhorar a circulação e proporcionar sensação de leveza e bem-estar.</p>
                    <span class="price">R$ 200,00</span>
                    <a href="agendar.php?servico=Drenagem" class="btn-schedule">Agendar agora</a>
                </div>
                <div class="service-img-block">
                    <img src="../img/drenagem.png" alt="Drenagem">
                </div>
            </div>

            <div class="service-row">
                <div class="service-info-block">
                    <h2>SESSÕES DE REIKI</h2>
                    <p>As sessões de Reiki promovem relaxamento, equilíbrio energético, atuam reduzindo o estresse, a ansiedade e renovam as energias.</p>
                    <span class="price">R$ 150,00</span>
                    <a href="agendar.php?servico=Sessões de Reiki" class="btn-schedule">Agendar agora</a>
                </div>
                <div class="service-img-block">
                    <img src="../img/sessoes-reiki.png" alt="Sessões de Reiki">
                </div>
            </div>

            <div class="service-row reverse">
                <div class="service-info-block">
                    <h2>Brow Lamination</h2>
                    <p>O brow lamination é um procedimento que alinha e modela os fios da sobrancelha, deixando um efeito cheio, preenchido, natural e elegante.</p>
                    <span class="price">R$ 200,00</span>
                    <a href="agendar.php?servico=Brow Lamination" class="btn-schedule">Agendar agora</a>
                </div>
                <div class="service-img-block">
                    <img src="../img/brown-lomination.png" alt="Brow Lamination">
                </div>
            </div>

            <div class="service-row">
                <div class="service-info-block">
                    <h2>Extensão de cílios</h2>
                    <p>A extensão de cílios realça o olhar, trazendo mais volume, alongamento e praticidade para o dia a dia.</p>
                    <span class="price">R$ 180,00</span>
                    <a href="agendar.php?servico=Extensão de cílios" class="btn-schedule">Agendar agora</a>
                </div>
                <div class="service-img-block">
                    <img src="../img/extensao-cilios.png" alt="Extensão de cílios">
                </div>
            </div>

            <div class="service-row reverse">
                <div class="service-info-block">
                    <h2>Micropigmentação</h2>
                    <p>A micropigmentação realça e define as sobrancelhas, lábios ou olhos, trazendo mais praticidade, harmonia e autoestima.</p>
                    <span class="price">R$ 450,00</span>
                    <a href="agendar.php?servico=Micropigmentação" class="btn-schedule">Agendar agora</a>
                </div>
                <div class="service-img-block">
                    <img src="../img/micropigmentacao.png" alt="Micropigmentação">
                </div>
            </div>

            <div class="service-row">
                <div class="service-info-block">
                    <h2>Limpeza de pele</h2>
                    <p>A limpeza de pele remove impurezas, controla a oleosidade e deixa a pele mais saudável, leve e revitalizada.</p>
                    <span class="price">R$ 150,00</span>
                    <a href="agendar.php?servico=Limpeza de pele" class="btn-schedule">Agendar agora</a>
                </div>
                <div class="service-img-block">
                    <img src="../img/limpeza-pele.png" alt="Limpeza de pele">
                </div>
            </div>

            <div class="service-row reverse">
                <div class="service-info-block">
                    <h2>Sessão de laser</h2>
                    <p>As sessões de laser ajudam na remoção de pelos e no cuidado da pele, proporcionando mais praticidade, confiança e autoestima.</p>
                    <span class="price">R$ 250,00</span>
                    <a href="agendar.php?servico=Sessão de laser" class="btn-schedule">Agendar agora</a>
                </div>
                <div class="service-img-block">
                    <img src="../img/sessao-laser.png" alt="Sessão de laser">
                </div>
            </div>

        </section>
    </main>

    <!-- Rodapé Padrão -->
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

    <!-- Script de Interatividade -->
    <script src="../js/main.js"></script>
</body>
</html>