<?php
session_start();
session_destroy();
header("Location: login-admin.php");
exit;
?>