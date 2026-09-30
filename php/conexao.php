<?php
// Configurações de acesso ao banco de dados MySQL
$host = "localhost";   // Endereço do servidor
$usuario = "root";     // Nome de usuário do banco
$senha = "";           // Senha de acesso (vazia por padrão em ambientes locais)
$banco = "aurabela";   // Nome do banco de dados

// Criação da conexão utilizando a extensão MySQLi
$conn = new mysqli($host, $usuario, $senha, $banco);

// Verifica se ocorreu algum erro na tentativa de conexão
if ($conn->connect_error) {
    // Interrompe a execução do script e exibe a mensagem de erro detalhada
    die("Erro na conexão: " . $conn->connect_error);
}

// Define o charset da conexão para utf8mb4, garantindo suporte correto a acentos e caracteres especiais
$conn->set_charset('utf8mb4');
?>