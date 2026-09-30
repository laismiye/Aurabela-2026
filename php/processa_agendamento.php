<?php
// Habilita a exibição de erros no PHP para facilitar o depuramento em ambiente de desenvolvimento
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inicia a sessão para verificar a autenticação do usuário
session_start(); 
require 'conexao.php';

// Proteção da rota: redireciona para a página de login se o usuário não estiver autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../pages/login.php?erro=necessario_login");
    exit;
}

// Processa o envio dos dados via formulário POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['usuario_id'];
    
    // Higieniza as entradas recebidas para mitigar riscos de injeção no banco
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $sobrenome = mysqli_real_escape_string($conn, $_POST['sobrenome']);
    $telefone = mysqli_real_escape_string($conn, $_POST['telefone']);
    $servico = mysqli_real_escape_string($conn, $_POST['servico']);
    $data = mysqli_real_escape_string($conn, $_POST['data']);
    $horario = mysqli_real_escape_string($conn, $_POST['horario']);

    // Verifica se já existe um agendamento gravado para a mesma data e horário
    $check = $conn->prepare("SELECT id FROM agendamentos WHERE data_agendamento = ? AND horario_agendamento = ?");
    $check->bind_param("ss", $data, $horario);
    $check->execute();
    
    // Se a consulta retornar resultados, interrompe o fluxo prevenindo conflito de horários
    if ($check->get_result()->num_rows > 0) {
        die("Desculpe, esse horário acabou de ser preenchido por outro cliente. Volte e selecione outro.");
    }
    $check->close();

    // Prepara a query de inserção segura utilizando Prepared Statements
    $stmt = $conn->prepare("INSERT INTO agendamentos (usuario_id, nome, sobrenome, telefone, servico, data_agendamento, horario_agendamento) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    // Associa os parâmetros (i = inteiro, s = string)
    $stmt->bind_param("issssss", $usuario_id, $nome, $sobrenome, $telefone, $servico, $data, $horario);

    // Executa o agendamento e redireciona em caso de sucesso
    if ($stmt->execute()) {
        header("Location: ../pages/meus-agendamentos.php?status=sucesso");
        exit;
    } else {
        echo "Erro ao processar agendamento no banco de dados: " . $conn->error;
    }
    $stmt->close();
}
?>