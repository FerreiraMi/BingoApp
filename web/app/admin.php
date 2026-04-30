<?php
// admin.php
session_start();
require_once 'db.php';

//echo  password_hash("kirk", PASSWORD_DEFAULT);

// Processamento do login
if(isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if($user  && password_verify($password, $user['password'])) {
       $_SESSION['admin'] = $user['username'];
       header("Location: admin");
       exit;
    } else {
       $error = "Usuário ou senha inválidos!";
    }
}

// Se não estiver logado, exibe o formulário de login
if(!isset($_SESSION['admin'])) {
   ?>
   <!DOCTYPE html>
   <html lang="pt">
   <head>
     <meta charset="UTF-8">
     <title>Login Admin - Bingo Beneficente</title>
     <style>
       body { font-family: Arial, sans-serif; background-color: #f0f0f0; }
       .login { max-width: 300px; margin: 50px auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
       input { width: 100%; padding: 10px; margin: 5px 0; }
       button { padding: 10px; width: 100%; }
     </style>
   </head>
   <body>
     <div class="login">
       <h2>Login Admin</h2>
       <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
       <form method="post" action="admin">
         <input type="text" name="username" placeholder="Usuário" required>
         <input type="password" name="password" placeholder="Senha" required>
         <button type="submit" name="login">Entrar</button>
       </form>
     </div>
   </body>
   </html>
   <?php
   exit;
}

// Se estiver logado, exibe o painel de administração
$stmt = $pdo->query("SELECT current_round FROM bingo_round LIMIT 1");
$currentRound = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Bingo Beneficente - Admin</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f0f0f0;
      width: 1024px;
      height: 768px;
      margin: 0 auto;
      padding: 20px;
    }
    .container {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    nav a {
      margin-right: 15px;
      text-decoration: none;
      color: blue;
    }
    h1 { text-align: center; }
    input[type="number"], input[type="text"] {
      font-size: 1.5em;
      padding: 10px;
      margin: 5px 0;
    }
    button {
      font-size: 1.2em;
      padding: 10px 20px;
      margin: 10px 0;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Menu de Navegação -->
    <nav>
      <a href="display">Display</a>
      <a href="admin">Admin</a>
      <a href="usuarios">Usuários</a>
      <a href="logout">Logout</a>
    </nav>
    <h1>Bingo Beneficente - Admin</h1>
    <p><strong>Rodada Atual:</strong> <?php echo $currentRound; ?></p>
    <form id="drawForm" method="post" action="submit_draw">
      <label for="draw">Número Sorteado (01 a 75):</label>
      <input type="number" id="draw" name="draw" min="1" max="75" required>
      <button type="submit">Registrar Número</button>
    </form>
    <br>
    <label for="winners">Ganhadores da Rodada:</label>
    <input type="text" id="winners" name="winners" placeholder="Digite os nomes separados por vírgula">
    <br>
    <button id="finalizeRound">Finalizar Rodada</button>
  </div>

  <script>
    var ws = new WebSocket("<?= getenv('WS_ADDRESS') ?: 'ws://localhost:8080' ?>");

    ws.onopen = function() {
      console.log("Conexão WebSocket estabelecida.");
    };

    document.getElementById('drawForm').addEventListener('submit', function(e){
      e.preventDefault();
      var drawNumber = parseInt(document.getElementById('draw').value);
      if(drawNumber < 1 || drawNumber > 75){
        alert("Número inválido! Escolha um número entre 1 e 75.");
        return;
      }
      var xhr = new XMLHttpRequest();
      xhr.open("POST", "submit_draw", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.onreadystatechange = function() {
         if (xhr.readyState == 4 && xhr.status == 200) {
            var data = JSON.parse(xhr.responseText);
            if(data.success){
              ws.send(JSON.stringify({action: "new_draw", number: drawNumber}));
              document.getElementById('draw').value = '';
            } else {
              alert(data.message);
            }
         }
      };
      xhr.send("draw=" + drawNumber);
    });

    document.getElementById('finalizeRound').addEventListener('click', function(){
      var winners = document.getElementById('winners').value;
      var xhr = new XMLHttpRequest();
      xhr.open("POST", "clear_round", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.onreadystatechange = function() {
         if(xhr.readyState == 4 && xhr.status == 200){
            alert(xhr.responseText);
            ws.send(JSON.stringify({action: "clear_round", winners: winners}));
            location.reload();
         }
      };
      xhr.send("winners=" + encodeURIComponent(winners));
    });
  </script>
</body>
</html>
