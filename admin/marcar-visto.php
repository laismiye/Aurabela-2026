<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'nao_autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    echo json_encode(['erro' => 'metodo_nao_permitido']);
    exit;
}

$tem_id = isset($_POST['id']);
$tem_todos = isset($_POST['todos']);
$marcar_todos = $tem_todos && $_POST['todos'] === '1';

if (($tem_id && $tem_todos) || (!$tem_id && !$marcar_todos)) {
    http_response_code(400);
    echo json_encode(['erro' => 'informe_id_ou_todos']);
    exit;
}

$id = null;
if ($tem_id) {
    $id_recebido = is_string($_POST['id']) ? $_POST['id'] : '';
    $id = filter_var($id_recebido, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        http_response_code(400);
        echo json_encode(['erro' => 'id_invalido']);
        exit;
    }
}

require '../php/conexao.php';

if ($marcar_todos) {
    $stmt = $conn->prepare("UPDATE agendamentos SET visto = 1 WHERE visto = 0");
} else {
    $stmt = $conn->prepare("UPDATE agendamentos SET visto = 1 WHERE id = ? AND visto = 0");
    if ($stmt) {
        $stmt->bind_param('i', $id);
    }
}

if (!$stmt || !$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['erro' => 'falha_ao_marcar_como_visto']);
    exit;
}

$afetados = $stmt->affected_rows;
$stmt->close();

echo json_encode(['sucesso' => true, 'afetados' => $afetados]);
