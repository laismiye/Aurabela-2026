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
$agendamentos = $conn->query("SELECT id, nome, sobrenome, telefone, servico, data_agendamento, horario_agendamento, status FROM agendamentos ORDER BY FIELD(status, 'Agendado', 'Concluído', 'Cancelado'), data_agendamento ASC, horario_agendamento ASC");

// Consultas para os cards de estatísticas (métricas rápidas)
$total_usuarios = $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'];
$total_newsletter = $conn->query("SELECT COUNT(*) as total FROM newsletter")->fetch_assoc()['total'];
$total_agendamentos = $conn->query("SELECT COUNT(*) as total FROM agendamentos WHERE status = 'Agendado'")->fetch_assoc()['total'];
$total_cancelados = $conn->query("SELECT COUNT(*) as total FROM agendamentos WHERE status = 'Cancelado'")->fetch_assoc()['total'];
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
    <link rel="stylesheet" href="../css/style.css?v=2">
</head>
<body>

    <!-- Barra de navegação superior com dados do admin logado -->
    <nav class="dash-navbar">
        <div class="logo-text">Aura Bela</div>
        <div class="navbar-right">
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
            <div class="stat-card"><p class="stat-label">Usuários Cadastrados</p><p class="stat-value"><?= $total_usuarios ?></p></div>
            <div class="stat-card"><p class="stat-label">Inscritos Newsletter</p><p class="stat-value"><?= $total_newsletter ?></p></div>
            <div class="stat-card"><p class="stat-label">Agendamentos Ativos</p><p class="stat-value"><?= $total_agendamentos ?></p></div>
            <div class="stat-card"><p class="stat-label">Cancelados</p><p class="stat-value"><?= $total_cancelados ?></p></div>
        </div>

        <!-- Botões de navegação por abas -->
        <div class="tabs-container">
            <button id="btn-agendamento" class="tab-btn active" onclick="switchTab('tab-agendamentos')">Agendamentos</button>
            <button id="btn-usuario" class="tab-btn" onclick="switchTab('tab-usuarios')">Usuários registrados</button>
            <button id="btn-newsletter" class="tab-btn" onclick="switchTab('tab-newsletter')">Newsletter</button>
        </div>

        <!-- Aba: Agendamentos -->
        <div id="tab-agendamentos" class="tab-panel active">
            <h2>Próximos Agendamentos Cadastrados</h2>
            <?php if ($agendamentos->num_rows > 0): ?>
            <table>
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
                <tbody>
                    <?php while ($ag = $agendamentos->fetch_assoc()): 
                        // Aplica estilo diferenciado visualmente caso o agendamento esteja cancelado
                        $status_atual = $ag['status'] ? $ag['status'] : 'Agendado';
                        $classe_status = ($status_atual === 'Cancelado') ? 'status-cancelado' : 'status-agendado';
                        $estilo_linha = ($status_atual === 'Cancelado') ? 'style="opacity: 0.5; background-color: #fafafa;"' : '';
                    ?>
                    <tr <?= $estilo_linha ?>>
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
            <?php else: ?>
                <p class="empty-msg">Nenhum agendamento marcado até o momento.</p>
            <?php endif; ?>
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

    </div>

    <!-- Script para controle de troca de abas e modal de confirmação -->
    <script src="../js/main.js?v=2"></script>
</body>
</html>