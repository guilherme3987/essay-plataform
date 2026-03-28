<?php
session_start();
session_destroy();
// Adicionado espaço obrigatório após "Location:" por padrão HTTP
header('Location: ../pages/login.php');
exit;
?>