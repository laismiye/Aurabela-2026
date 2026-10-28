<?php
// Inicia a sessão para permitir o armazenamento dos dados do usuário autenticado
session_start();

// Inclui o arquivo de conexão com o banco de dados
require 'conexao.php';

// Verifica se a requisição veio via POST e se os campos de e-mail e senha foram fornecidos
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['email'], $_POST['senha'])) {
    header("Location: ../pages/login.php");
    exit;
}

// Resgata os dados digitados no formulário
$email = $_POST['email'];
$senha = $_POST['senha'];

// Prepara a consulta SQL para buscar o usuário pelo e-mail (evita SQL Injection)
$sql = "SELECT * FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

// Valida se o usuário foi encontrado e se a senha digitada corresponde ao hash salvo no banco
if ($usuario && password_verify($senha, $usuario['senha'])) {
    // Salva os dados do usuário na sessão ativa
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    
    // Redireciona para a página inicial após o login com sucesso
    header("Location: ../index.php");
    exit;
} else {
    // Redireciona de volta para a página de login com indicador de erro
    header("Location: ../pages/login.php?erro=1");
    exit;
}
?>