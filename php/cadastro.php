<?php
// Inclui o arquivo de conexão com o banco de dados
require 'conexao.php';

// Verifica se a requisição veio via POST e se todos os campos obrigatórios foram enviados
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['nome'], $_POST['email'], $_POST['senha'], $_POST['confirmar_senha'])) {
    // Redireciona para a página de login caso o acesso seja direto ou faltem campos
    header("Location: ../pages/login.php");
    exit;
}

// Resgata os dados enviados pelo formulário
$nome = $_POST['nome'];
$email = $_POST['email'];
$senha = $_POST['senha'];
$confirmarSenha = $_POST['confirmar_senha'];

// Valida se a senha e a confirmação de senha correspondem
if ($senha !== $confirmarSenha) {
    // Redireciona de volta com parâmetro de erro na URL
    header("Location: ../pages/login.php?erro=1");
    exit;
}

// Gera um hash seguro da senha utilizando o algoritmo padrão do PHP (Bcrypt / Argon2)
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

// Prepara a instrução SQL para inserção segura contra SQL Injection utilizando Prepared Statements
$sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $nome, $email, $senhaHash);

// Executa a query no banco de dados
if ($stmt->execute()) {
    // Redireciona o usuário para o login indicando sucesso no cadastro
    header("Location: ../pages/login.php?cadastro=sucesso");
    exit;
} else {
    // Exibe mensagem de erro caso ocorra falha na execução
    echo "Erro: " . $conn->error;
}
?>