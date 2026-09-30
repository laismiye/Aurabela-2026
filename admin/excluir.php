<?php
// Proteção da página: garante que apenas admins autenticados executem ações
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login-admin.php");
    exit;
}

require '../php/conexao.php';

// Processa o pedido de exclusão recebido via formulário POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['tipo'])) {
    $id = intval($_POST['id']);
    $tipo = $_POST['tipo'];

    // Mapeia a tabela correta baseada no tipo recebido (evita SQL Injection dinamizando nomes de tabelas de forma controlada)
    switch ($tipo) {
        case 'usuario':
            $tabela = 'usuarios';
            $coluna_id = 'id';
            break;
        case 'newsletter':
            $tabela = 'newsletter';
            $coluna_id = 'id';
            break;
        case 'agendamento':
            $tabela = 'agendamentos';
            $coluna_id = 'id';
            break;
        default:
            header("Location: dashboard.php?erro=tipo_invalido");
            exit;
    }

    // Executa a remoção do registro usando Prepared Statement
    $stmt = $conn->prepare("DELETE FROM $tabela WHERE $coluna_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redireciona mantendo a aba do item excluído ativa
        header("Location: dashboard.php?sucesso=deletado&aba=" . $tipo);
    } else {
        header("Location: dashboard.php?erro=falha_deletar");
    }
    
    $stmt->close();
    exit;
}

// Redireciona por padrão caso a requisição não seja válida
header("Location: dashboard.php");
exit;