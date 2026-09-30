<?php
// Configurações para exibição de erros durante o desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Verifica se o usuário está logado; caso contrário, redireciona para o login
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php?form=login');
    exit;
}

include '../php/conexao.php'; 

// Garante a padronização do nome da variável de conexão com o banco
if (isset($conexao) && !isset($conn)) {
    $conn = $conexao;
}

$usuario_id = $_SESSION['usuario_id'];
$msg = "";
$msg_erro = "";

// --- Variáveis de Cabeçalho ---
$usuario_logado = true; // Confirma estado autenticado
$usuario_nome = "Usuário"; // Valor padrão caso o nome não seja localizado

// Consulta o nome do usuário logado para personalização da interface
$query_user = "SELECT nome FROM usuarios WHERE id = '$usuario_id' LIMIT 1"; 
$result_user = mysqli_query($conn, $query_user);
if ($result_user && mysqli_num_rows($result_user) > 0) {
    $user_data = mysqli_fetch_assoc($result_user);
    $usuario_nome = $user_data['nome'];
}

// --- Processamento de Cancelamento ---
if (isset($_GET['cancelar_id'])) {
    $id_agendamento = mysqli_real_escape_string($conn, $_GET['cancelar_id']);
    // Atualiza o status para 'Cancelado' garantindo que o agendamento pertença ao usuário logado
    $delete_query = "UPDATE agendamentos SET status = 'Cancelado' WHERE id = '$id_agendamento' AND usuario_id = '$usuario_id'";
    
    if (mysqli_query($conn, $delete_query)) {
        header('Location: meus-agendamentos.php?status=cancelado');
        exit;
    } else {
        $msg_erro = "Erro ao cancelar: " . mysqli_error($conn);
    }
}

// Mensagens de feedback baseadas na URL
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'cancelado') {
        $msg = "Agendamento cancelado com sucesso!";
    } elseif ($_GET['status'] === 'sucesso') {
        $msg = "Perfeito! Seu agendamento foi confirmado e já está na nossa lista!";
    }
}

// Consulta de agendamentos futuros e ativos
$query_ativos = "SELECT id, servico, DATE_FORMAT(data_agendamento, '%d/%m/%Y') as data_formatada, TIME_FORMAT(horario_agendamento, '%H:%i') as hora_formatada 
                 FROM agendamentos 
                 WHERE usuario_id = '$usuario_id' 
                 AND data_agendamento >= CURDATE() 
                 AND status = 'Agendado' 
                 ORDER BY data_agendamento ASC, horario_agendamento ASC";

$result_ativos = mysqli_query($conn, $query_ativos);

// Consulta do histórico (datas passadas, concluídos ou cancelados)
$query_historico = "SELECT id, servico, DATE_FORMAT(data_agendamento, '%d/%m/%Y') as data_formatada, TIME_FORMAT(horario_agendamento, '%H:%i') as hora_formatada, status 
                    FROM agendamentos 
                    WHERE usuario_id = '$usuario_id' 
                    AND (data_agendamento < CURDATE() OR status = 'Concluído' OR status = 'Cancelado') 
                    ORDER BY data_agendamento DESC, horario_agendamento DESC";

$result_historico = mysqli_query($conn, $query_historico);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Agendamentos - Aura Bela</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css?v=3">
</head>
<body>
    
    <!-- Cabeçalho principal com menu de usuário -->
    <header class="navbar">
        <div class="logo-text">Aura Bela</div>
        <nav class="nav-links">
            <a href="../index.php">Início</a>
            <a href="quem-somos.php">Quem somos</a>
            <a href="reiki.php">Reiki</a>
            <a href="servicos.php">Serviços</a>

            <div class="profile-container">
                <?php if ($usuario_logado): ?>
                    <span class="profile-username">Olá! <?php echo htmlspecialchars($usuario_nome); ?></span>
                <?php endif; ?>
                <a href="<?php echo $usuario_logado ? '#' : 'login.php?form=login'; ?>" class="profile-icon" id="profileDropdownBtn" title="<?php echo $usuario_logado ? 'Minha Conta' : 'Login / Cadastro'; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </a>

                <!-- Submenu suspenso da conta -->
                <?php if ($usuario_logado): ?>
                    <div class="profile-dropdown" id="profileDropdownMenu">
                        <a href="minha-conta.php">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            Minha Conta
                        </a>
                        <a href="meus-agendamentos.php" class="active">
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

    <div class="page-wrapper">
        <h1 class="section-title-page">Painel de Agendamentos</h1>

        <!-- Alertas de sucesso ou erro -->
        <?php if (!empty($msg)): ?>
            <div class="alert-success"><?php echo $msg; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($msg_erro)): ?>
            <div class="alert-error"><?php echo $msg_erro; ?></div>
        <?php endif; ?>

        <!-- Seção: Agendamentos Ativos -->
        <div class="schedule-section">
            <h2>Seus Próximos Compromissos</h2>
            <?php if (!$result_ativos || mysqli_num_rows($result_ativos) == 0): ?>
                <div class="empty-state">
                    <p>Você não possui nenhum agendamento marcado no momento.</p>
                </div>
            <?php else: ?>
                <?php while ($agend = mysqli_fetch_assoc($result_ativos)): ?>
                    <div class="card-appointment">
                        <div class="appointment-details">
                            <h3><?php echo htmlspecialchars($agend['servico']); ?></h3>
                            <p><strong>Data:</strong> <?php echo $agend['data_formatada']; ?> às <?php echo $agend['hora_formatada']; ?>h</p>
                        </div>
                        <div>
                            <!-- Ação de cancelamento com confirmação via JavaScript -->
                            <a href="meus-agendamentos.php?cancelar_id=<?php echo $agend['id']; ?>" class="btn-cancel" onclick="return confirm('Tem certeza de que deseja cancelar este agendamento?')">Cancelar</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <!-- Seção: Histórico -->
        <div class="schedule-section">
            <h2>Histórico de Atendimentos</h2>
            <?php if (!$result_historico || mysqli_num_rows($result_historico) == 0): ?>
                <div class="empty-state">
                    <p>Nenhum atendimento histórico registrado.</p>
                </div>
            <?php else: ?>
                <?php while ($h = mysqli_fetch_assoc($result_historico)): ?>
                    <?php 
                        $badge_class = ($h['status'] === 'Concluído') ? 'status-concluido' : 'status-cancelado-user';
                    ?>
                    <div class="card-appointment" style="opacity: 0.75; border-left-color: #aaa;">
                        <div class="appointment-details">
                            <h3><?php echo htmlspecialchars($h['servico']); ?></h3>
                            <p><strong>Data:</strong> <?php echo $h['data_formatada']; ?> às <?php echo $h['hora_formatada']; ?>h</p>
                        </div>
                        <div>
                            <span class="status-badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($h['status']); ?></span>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="../js/main.js?v=2"></script>
</body>
</html>