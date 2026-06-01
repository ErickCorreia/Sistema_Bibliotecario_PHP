<?php
// logout.php
session_start();

// Destrói todas as variáveis de sessão
$_SESSION = array();

// Destrói a sessão física no servidor
session_destroy();

// Redireciona para a página inicial (pública)
header("Location: index.php");
exit;
?>