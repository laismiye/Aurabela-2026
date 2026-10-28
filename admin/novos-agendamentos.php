<?php
// Endpoint consultado pelo dashboard para buscar agendamentos criados depois do último exibido (somente leitura)
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'nao_autorizado']);
    exit;
}

require '../php/conexao.php';

$desde = isset($_GET['desde']) ? (int) $_GET['desde'] : 0;

$stmt = $conn->prepare("SELECT id, nome, sobrenome, telefone, servico, data_agendamento, horario_agendamento, status FROM agendamentos WHERE id > ? ORDER BY id ASC");
$stmt->bind_param("i", $desde);
$stmt->execute();
$resultado = $stmt->get_result();

$novos = [];
while ($ag = $resultado->fetch_assoc()) {
    $novos[] = [
        'id'       => (int) $ag['id'],
        'cliente'  => trim($ag['nome'] . ' ' . $ag['sobrenome']),
        'telefone' => $ag['telefone'],
        'servico'  => $ag['servico'],
        'data'     => date('d/m/Y', strtotime($ag['data_agendamento'])),
        'horario'  => date('H:i', strtotime($ag['horario_agendamento'])),
        'status'   => $ag['status'] ? $ag['status'] : 'Agendado',
    ];
}
$stmt->close();

$total_ativos = $conn->query("SELECT COUNT(*) as total FROM agendamentos WHERE status = 'Agendado'")->fetch_assoc()['total'];

echo json_encode(['novos' => $novos, 'total_ativos' => (int) $total_ativos]);
