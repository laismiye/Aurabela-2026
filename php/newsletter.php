<?php
// Inclui o arquivo de conexão com o banco de dados
require 'conexao.php';

// Verifica se a requisição é do tipo POST e se o campo de e-mail não está vazio
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['email'])) {
    echo 'erro';
    exit;
}

// Remove espaços em branco do início e do fim do e-mail digitado
$email = trim($_POST['email']);

// Valida o formato do e-mail usando o filtro nativo do PHP
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo 'erro';
    exit;
}

// Prepara a consulta SQL. O "INSERT IGNORE" evita erros caso o e-mail já esteja cadastrado no banco (se houver chave única)
$sql = "INSERT IGNORE INTO newsletter (email) VALUES (?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo 'erro';
    exit;
}

// Associa o parâmetro e executa a instrução
$stmt->bind_param("s", $email);
$stmt->execute();

// Retorna 'ok' para que o script JavaScript/AJAX no frontend confirme o sucesso ao usuário
echo 'ok';
?>