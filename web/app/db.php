<?php
// db.php
$host = getenv('DB_HOST') ?: 'db';
$port = getenv('DB_PORT') ?: '5432';
$db   = getenv('DB_NAME') ?: 'nome_do_banco';
$user = getenv('DB_USER') ?: 'usuario';
$pass = getenv('DB_PASS') ?: 'senha';
$dsn = "pgsql:host=$host;port=$port;dbname=$db;";

try {
    $pdo = new PDO($dsn, $user, $pass);
    // Configura erros para exceções
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>
