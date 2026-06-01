<?php
// config.php
session_start(); // Inicia a sessão para controle de login

$host = 'localhost';
$dbname = 'biblioteca_a3';
$user = 'root'; 
$pass = 'your_new_password';   

try {
    // Conexão usando PDO para maior segurança (evita SQL Injection)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    // Configura o PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro de conexão com o banco de dados: " . $e->getMessage());
}
?>