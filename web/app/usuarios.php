<?php
// usuarios.php
session_start();
require_once 'db.php';

// Verifica se o usuário está logado; caso não, redireciona para a área de login (admin)
if(!isset($_SESSION['admin'])) {
    header("Location: login");
    exit;
}

$error = '';
$success = '';

if(isset($_POST['submit'])){
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    if(empty($username) || empty($password)){
        $error = "Preencha todos os campos.";
    } else {
        // Verifica se o usuário já existe
        $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        if($stmt->fetch()){
            $error = "Usuário já existe!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admin_users (username, password) VALUES (?, ?)");
            if($stmt->execute([$username, $hash])){
                $success = "Usuário criado com sucesso.";
            } else {
                $error = "Erro ao criar usuário.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f0f0f0; }
        .container { max-width: 500px; margin: 50px auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin: 5px 0; }
        button { padding: 10px; width: 100%; }
        .message { color: green; }
        .error { color: red; }
        nav a { margin-right: 15px; text-decoration: none; color: blue; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Menu de navegação -->
        <nav>
            <a href="display">Display</a>
            <a href="admin">Admin</a>
            <a href="usuarios">Usuários</a>
            <a href="logout">Logout</a>
        </nav>
        <h2>Adicionar Novo Usuário</h2>
        <?php if($error) echo "<p class='error'>$error</p>"; ?>
        <?php if($success) echo "<p class='message'>$success</p>"; ?>
        <form method="post" action="usuarios">
            <input type="text" name="username" placeholder="Nome de Usuário" required>
            <input type="password" name="password" placeholder="Senha" required>
            <button type="submit" name="submit">Criar Usuário</button>
        </form>
        <p><a href="admin">Voltar ao Painel de Administração</a></p>
    </div>
</body>
</html>
