<?php
// Inicia a sessão e conecta ao banco de dados
session_start();
require '../php/conexao.php';

// Captura as credenciais enviadas pelo formulário
$email = $_POST['email'];
$senha = $_POST['senha'];

// Busca o administrador pelo e-mail (Prepared Statement para evitar SQL Injection)
$sql = "SELECT * FROM admins WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();
$admin = $resultado->fetch_assoc();

// Valida a senha hash e grava as variáveis na sessão em caso de sucesso
if ($admin && password_verify($senha, $admin['senha'])) {
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_email'] = $admin['email'];
    header("Location: dashboard.php");
} else {
    // Redireciona de volta com flag de erro se a autenticação falhar
    header("Location: login-admin.php?erro=1");
}
exit;
?>