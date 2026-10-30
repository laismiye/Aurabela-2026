<?php
// Endpoint somente de leitura para listar agendamentos ainda não vistos no dashboard.
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'nao_autorizado']);
    exit;
}

require '../php/conexao.php';

$stmt = $conn->prepare("SELECT id, nome, sobrenome, telefone, servico, data_agendamento, horario_agendamento, status FROM agendamentos WHERE visto = 0 ORDER BY id DESC");
if (!$stmt || !$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['erro' => 'falha_ao_buscar_agendamentos']);
    exit;
}

$novos = [];
$resultado = $stmt->get_result();
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

$status_cancelado = 'Cancelado';
$totais = $conn->prepare("
    SELECT
        (SELECT COUNT(*) FROM agendamentos WHERE COALESCE(status, 'Agendado') <> ?) AS total_ativos,
        (SELECT COUNT(*) FROM usuarios) AS total_usuarios,
        (SELECT COUNT(*) FROM newsletter) AS total_newsletter,
        (SELECT COUNT(*) FROM agendamentos WHERE status = ?) AS total_cancelados
");
if (!$totais) {
    http_response_code(500);
    echo json_encode(['erro' => 'falha_ao_buscar_totais']);
    exit;
}
$totais->bind_param('ss', $status_cancelado, $status_cancelado);
if (!$totais->execute()) {
    http_response_code(500);
    echo json_encode(['erro' => 'falha_ao_buscar_totais']);
    exit;
}
$contagens = $totais->get_result()->fetch_assoc();

echo json_encode([
    'novos' => $novos,
    'total_ativos' => (int) $contagens['total_ativos'],
    'total_novos' => count($novos),
    'total_usuarios' => (int) $contagens['total_usuarios'],
    'total_newsletter' => (int) $contagens['total_newsletter'],
    'total_cancelados' => (int) $contagens['total_cancelados'],
], JSON_INVALID_UTF8_SUBSTITUTE);
