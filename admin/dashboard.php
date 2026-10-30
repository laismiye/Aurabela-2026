<?php
// Proteção da página: verifica se o admin está autenticado
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login-admin.php");
    exit;
}
require '../php/conexao.php';

// Consultas principais para listagem nas tabelas (Agendamentos ordenados por status e data)
$usuarios = $conn->query("SELECT id, nome, email, criado_em FROM usuarios ORDER BY criado_em DESC");
$newsletter = $conn->query("SELECT id, email, inscrito_em FROM newsletter ORDER BY inscrito_em DESC");
$status_cancelado = 'Cancelado';
$stmt_agendamentos = $conn->prepare("SELECT id, nome, sobrenome, telefone, servico, data_agendamento, horario_agendamento, status FROM agendamentos WHERE COALESCE(status, 'Agendado') <> ? ORDER BY FIELD(status, 'Agendado', 'Concluído'), data_agendamento ASC, horario_agendamento ASC");
if (!$stmt_agendamentos) {
    error_log('Não foi possível preparar a consulta de agendamentos ativos: ' . $conn->error);
    http_response_code(500);
    exit('Não foi possível carregar os agendamentos.');
}
$stmt_agendamentos->bind_param('s', $status_cancelado);
if (!$stmt_agendamentos->execute()) {
    error_log('Não foi possível consultar os agendamentos ativos: ' . $stmt_agendamentos->error);
    http_response_code(500);
    exit('Não foi possível carregar os agendamentos.');
}
$agendamentos = $stmt_agendamentos->get_result();

$stmt_cancelados = $conn->prepare("SELECT id, nome, sobrenome, telefone, servico, data_agendamento, horario_agendamento FROM agendamentos WHERE status = ? ORDER BY data_agendamento DESC, horario_agendamento DESC, id DESC");
if (!$stmt_cancelados) {
    error_log('Não foi possível preparar a consulta de agendamentos cancelados: ' . $conn->error);
    http_response_code(500);
    exit('Não foi possível carregar os agendamentos cancelados.');
}
$stmt_cancelados->bind_param('s', $status_cancelado);
if (!$stmt_cancelados->execute()) {
    error_log('Não foi possível consultar os agendamentos cancelados: ' . $stmt_cancelados->error);
    http_response_code(500);
    exit('Não foi possível carregar os agendamentos cancelados.');
}
$cancelados = $stmt_cancelados->get_result();

// Consultas para os cards de estatísticas (métricas rápidas)
$total_usuarios = $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'];
$total_newsletter = $conn->query("SELECT COUNT(*) as total FROM newsletter")->fetch_assoc()['total'];
$stmt_totais_agendamentos = $conn->prepare("SELECT SUM(COALESCE(status, 'Agendado') <> ?) AS total_agendamentos, SUM(status = ?) AS total_cancelados FROM agendamentos");
if (!$stmt_totais_agendamentos) {
    error_log('Não foi possível preparar a consulta de totais dos agendamentos: ' . $conn->error);
    http_response_code(500);
    exit('Não foi possível carregar os totais dos agendamentos.');
}
$stmt_totais_agendamentos->bind_param('ss', $status_cancelado, $status_cancelado);
if (!$stmt_totais_agendamentos->execute()) {
    error_log('Não foi possível consultar os totais dos agendamentos: ' . $stmt_totais_agendamentos->error);
    http_response_code(500);
    exit('Não foi possível carregar os totais dos agendamentos.');
}
$totais_agendamentos = $stmt_totais_agendamentos->get_result()->fetch_assoc();
$total_agendamentos = (int) $totais_agendamentos['total_agendamentos'];
$total_cancelados = (int) $totais_agendamentos['total_cancelados'];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aura Bela - Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>">
</head>
<body>

    <!-- Barra de navegação superior com dados do admin logado -->
    <nav class="dash-navbar">
        <div class="logo-text">Aura Bela</div>
        <div class="navbar-right">
            <div class="notification-container">
                <button type="button" class="notification-bell" id="notificationBell" aria-label="Notificações de novos agendamentos" aria-expanded="false" aria-controls="notificationDropdown">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M10 21h4"></path></svg>
                    <span class="notification-count" id="notificationCount" hidden>0</span>
                </button>
                <div class="notification-dropdown" id="notificationDropdown" hidden>
                    <div class="notification-header">
                        <strong>Novos agendamentos</strong>
                        <button type="button" class="notification-mark-all" id="markAllSeen" hidden>Marcar todos como vistos</button>
                    </div>
                    <p class="notification-error" id="notificationError" role="alert" hidden></p>
                    <div class="notification-list" id="notificationList" aria-live="polite">
                        <p class="notification-empty">Carregando agendamentos...</p>
                    </div>
                </div>
            </div>
            <span class="admin-badge"><?= htmlspecialchars($_SESSION['admin_email']) ?></span>
            <a href="logout-admin.php" class="btn-logout">Sair</a>
        </div>
    </nav>

    <div class="dash-content">
        <h1 class="dash-title">Painel de Controle</h1>

        <!-- Feedback visual para ações de exclusão -->
        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 'deletado'): ?>
            <div class="alert alert-success">Item removido com sucesso e atualizado no banco de dados.</div>
        <?php endif; ?>
        <?php if (isset($_GET['erro'])): ?>
            <div class="alert alert-error">Ocorreu um erro ao tentar remover o item. Tente novamente.</div>
        <?php endif; ?>

        <!-- Cards de estatísticas gerais -->
        <div class="stats-grid">
            <div class="stat-card"><p class="stat-label">Usuários Cadastrados</p><p class="stat-value" id="statUsuarios"><?= $total_usuarios ?></p></div>
            <div class="stat-card"><p class="stat-label">Inscritos Newsletter</p><p class="stat-value" id="statNewsletter"><?= $total_newsletter ?></p></div>
            <div class="stat-card"><p class="stat-label">Agendamentos Ativos</p><p class="stat-value" id="statAtivos"><?= $total_agendamentos ?></p></div>
            <div class="stat-card"><p class="stat-label">Cancelados</p><p class="stat-value" id="statCancelados"><?= $total_cancelados ?></p></div>
        </div>

        <!-- Botões de navegação por abas -->
        <div class="tabs-container">
            <button id="btn-agendamento" class="tab-btn active" onclick="switchTab('tab-agendamentos')">Agendamentos</button>
            <button id="btn-usuario" class="tab-btn" onclick="switchTab('tab-usuarios')">Usuários registrados</button>
            <button id="btn-newsletter" class="tab-btn" onclick="switchTab('tab-newsletter')">Newsletter</button>
            <button id="btn-cancelados" class="tab-btn" onclick="switchTab('tab-cancelados')">Cancelados</button>
        </div>

        <!-- Aba: Agendamentos -->
        <div id="tab-agendamentos" class="tab-panel active">
            <h2>Próximos Agendamentos Cadastrados</h2>
            <!-- A tabela fica sempre na página para receber os novos agendamentos em tempo real -->
            <table id="tabelaAgendamentos" class="<?= $agendamentos->num_rows > 0 ? '' : 'd-none' ?>">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Telefone</th>
                        <th>Serviço</th>
                        <th>Data/Hora</th>
                        <th>Status</th>
                        <th style="text-align: right;">Ações</th>
                    </tr>
                </thead>
                <tbody id="listaAgendamentos">
                    <?php while ($ag = $agendamentos->fetch_assoc()): 
                        $status_atual = $ag['status'] ? $ag['status'] : 'Agendado';
                        $classe_status = 'status-agendado';
                    ?>
                    <tr data-agendamento-id="<?= (int) $ag['id'] ?>">
                        <td><strong><?= htmlspecialchars($ag['nome'] . ' ' . $ag['sobrenome']) ?></strong></td>
                        <td><?= htmlspecialchars($ag['telefone']) ?></td>
                        <td><?= htmlspecialchars($ag['servico']) ?></td>
                        <td><span class="tag-date"><?= date('d/m/Y', strtotime($ag['data_agendamento'])) ?> às <?= date('H:i', strtotime($ag['horario_agendamento'])) ?>h</span></td>
                        <td><span class="status-badge <?= $classe_status ?>"><?= htmlspecialchars($status_atual) ?></span></td>
                        <td style="text-align: right;">
                            <!-- Form para remover o agendamento enviando o tipo via hidden -->
                            <form method="POST" action="excluir.php" onsubmit="return confirmarExclusao('este agendamento');" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $ag['id'] ?>">
                                <input type="hidden" name="tipo" value="agendamento">
                                <button type="submit" class="btn-delete">Remover</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <p class="empty-msg <?= $agendamentos->num_rows > 0 ? 'd-none' : '' ?>" id="agendamentosVazio">Nenhum agendamento marcado até o momento.</p>
        </div>

        <!-- Aba: Usuários -->
        <div id="tab-usuarios" class="tab-panel">
            <h2>Lista de Usuários Cadastrados</h2>
            <?php if ($usuarios->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome Completo</th>
                        <th>E-mail</th>
                        <th>Data de Registro</th>
                        <th style="text-align: right;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($u = $usuarios->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $u['id'] ?></td>
                        <td><strong><?= htmlspecialchars($u['nome']) ?></strong></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="tag-date"><?= date('d/m/Y \à\s H:i', strtotime($u['criado_em'])) ?></span></td>
                        <td style="text-align: right;">
                            <!-- Form para remover o usuário enviando o tipo via hidden -->
                            <form method="POST" action="excluir.php" onsubmit="return confirmarExclusao('este usuário e todos os acessos dele');" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="tipo" value="usuario">
                                <button type="submit" class="btn-delete">Remover</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p class="empty-msg">Nenhum usuário cadastrado ainda.</p>
            <?php endif; ?>
        </div>

        <!-- Aba: Newsletter -->
        <div id="tab-newsletter" class="tab-panel">
            <h2>Inscritos na Newsletter</h2>
            <?php if ($newsletter->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>E-mail do Assinante</th>
                        <th>Inscrito Em</th>
                        <th style="text-align: right;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($n = $newsletter->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $n['id'] ?></td>
                        <td><strong><?= htmlspecialchars($n['email']) ?></strong></td>
                        <td><span class="tag-date"><?= date('d/m/Y \à\s H:i', strtotime($n['inscrito_em'])) ?></span></td>
                        <td style="text-align: right;">
                            <!-- Form para remover da lista enviando o tipo via hidden -->
                            <form method="POST" action="excluir.php" onsubmit="return confirmarExclusao('este e-mail da lista da newsletter');" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $n['id'] ?>">
                                <input type="hidden" name="tipo" value="newsletter">
                                <button type="submit" class="btn-delete">Remover</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p class="empty-msg">Nenhum e-mail inscrito na newsletter ainda.</p>
            <?php endif; ?>
        </div>

        <!-- Aba: Agendamentos cancelados -->
        <div id="tab-cancelados" class="tab-panel">
            <h2>Agendamentos Cancelados</h2>
            <table id="tabelaCancelados" class="<?= $cancelados->num_rows > 0 ? '' : 'd-none' ?>">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Telefone</th>
                        <th>Serviço</th>
                        <th>Data/Hora</th>
                        <th>Status</th>
                        <th style="text-align: right;">Ações</th>
                    </tr>
                </thead>
                <tbody id="listaCancelados">
                    <?php while ($ag = $cancelados->fetch_assoc()): ?>
                    <tr data-agendamento-id="<?= (int) $ag['id'] ?>">
                        <td><strong><?= htmlspecialchars($ag['nome'] . ' ' . $ag['sobrenome']) ?></strong></td>
                        <td><?= htmlspecialchars($ag['telefone']) ?></td>
                        <td><?= htmlspecialchars($ag['servico']) ?></td>
                        <td><span class="tag-date"><?= date('d/m/Y', strtotime($ag['data_agendamento'])) ?> às <?= date('H:i', strtotime($ag['horario_agendamento'])) ?>h</span></td>
                        <td><span class="status-badge status-cancelado">CANCELADO</span></td>
                        <td style="text-align: right;">
                            <form method="POST" action="excluir.php" onsubmit="return confirmarExclusao('este agendamento');" style="display:inline;">
                                <input type="hidden" name="id" value="<?= (int) $ag['id'] ?>">
                                <input type="hidden" name="tipo" value="agendamento">
                                <button type="submit" class="btn-delete">Remover</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <p class="empty-msg <?= $cancelados->num_rows > 0 ? 'd-none' : '' ?>" id="canceladosVazio">Nenhum agendamento cancelado.</p>
        </div>

    </div>

    <!-- Script para controle de troca de abas e modal de confirmação -->
    <script src="../js/main.js?v=<?= filemtime(__DIR__ . '/../js/main.js') ?>"></script>
</body>
</html>