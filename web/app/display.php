<?php
// display.php
require_once 'db.php';

// Obtém a rodada atual
$stmt = $pdo->query("SELECT current_round FROM bingo_round LIMIT 1");
$currentRound = $stmt->fetchColumn();

// Obtém os números sorteados da rodada atual
$drawsStmt = $pdo->prepare("SELECT draw FROM bingo_draws WHERE round = ?");
$drawsStmt->execute([$currentRound]);
$currentDraws = $drawsStmt->fetchAll(PDO::FETCH_COLUMN);

// Se houver rodada anterior, obtém os ganhadores da rodada finalizada
$prevRound = ($currentRound > 1) ? $currentRound - 1 : null;
if($prevRound){
    $winnersStmt = $pdo->prepare("SELECT winners FROM bingo_winners WHERE round = ?");
    $winnersStmt->execute([$prevRound]);
    $prevWinners = $winnersStmt->fetchAll(PDO::FETCH_COLUMN);
} else {
    $prevWinners = [];
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Bingo Beneficente - Display</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #333;
      color: #fff;
      width: 1024px;
      height: 768px;
      margin: 0 auto;
      padding: 20px;
      overflow: hidden;
    }
    .container {
      background: #444;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.5);
    }
    h1 { text-align: center; margin-bottom: 20px; }
    table {
      width: 100%;
      border-collapse: collapse;
      text-align: center;
    }
    th, td {
      padding: 15px;
      border: 1px solid #666;
      font-size: 2em;
    }
    th { background-color: #555; }
    #winnersSection { margin-top: 20px; font-size: 1.5em; }
  </style>
</head>
<body>
  <div class="container">
    <h1>B I N G O</h1>
    <p><strong>Rodada Atual:</strong> <?php echo $currentRound; ?></p>
    <table id="bingoTable">
      <thead>
        <tr>
          <th>B</th>
          <th>I</th>
          <th>N</th>
          <th>G</th>
          <th>O</th>
        </tr>
      </thead>
      <tbody>
        <!-- Linhas serão inseridas via JavaScript -->
      </tbody>
    </table>
    <div id="winnersSection">
      <h2>Ganhadores da Última Rodada (<?php echo $prevRound ? $prevRound : 'N/A'; ?>):</h2>
      <p><?php echo implode(', ', $prevWinners); ?></p>
    </div>
  </div>

  <script>
    // Dados iniciais obtidos do banco
    var initialData = {
      currentRound: <?php echo json_encode($currentRound); ?>,
      draws: <?php echo json_encode($currentDraws); ?>
    };

    // Estrutura para armazenar os números sorteados por coluna
    var bingoData = { B: [], I: [], N: [], G: [], O: [] };

    // Função para determinar a coluna conforme o número (regras do bingo)
    function getBingoColumn(number) {
      if(number >= 1 && number <= 15) return 'B';
      else if(number >= 16 && number <= 30) return 'I';
      else if(number >= 31 && number <= 45) return 'N';
      else if(number >= 46 && number <= 60) return 'G';
      else if(number >= 61 && number <= 75) return 'O';
      else return null;
    }

    // Atualiza a tabela exibida
    function updateTable(){
      var maxRows = Math.max(bingoData.B.length, bingoData.I.length, bingoData.N.length, bingoData.G.length, bingoData.O.length);
      var tbody = document.getElementById('bingoTable').getElementsByTagName('tbody')[0];
      tbody.innerHTML = "";
      for(var i = 0; i < maxRows; i++){
        var row = document.createElement('tr');
        ['B', 'I', 'N', 'G', 'O'].forEach(function(letter) {
          var cell = document.createElement('td');
          cell.textContent = bingoData[letter][i] ? ("0" + bingoData[letter][i]).slice(-2) : "";
          row.appendChild(cell);
        });
        tbody.appendChild(row);
      }
    }

    // Preenche os dados iniciais da rodada atual
    initialData.draws.forEach(function(num) {
      var col = getBingoColumn(num);
      if(col && bingoData[col].indexOf(num) === -1) {
        bingoData[col].push(num);
      }
    });
    updateTable();

    // Conexão via WebSocket usando variável de ambiente
    var ws = new WebSocket("<?= getenv('WS_ADDRESS') ?: 'ws://localhost:8080' ?>");
    ws.onopen = function() {
      console.log("Conexão WebSocket estabelecida no display.");
    };

    ws.onmessage = function(event) {
      var message = JSON.parse(event.data);
      if(message.action === "new_draw") {
        var col = getBingoColumn(message.number);
        if(col && bingoData[col].indexOf(message.number) === -1){
          bingoData[col].push(message.number);
          updateTable();
        }
      } else if(message.action === "clear_round"){
        // Ao final da rodada, reinicia os dados (a página também pode ser recarregada)
        bingoData = { B: [], I: [], N: [], G: [], O: [] };
        updateTable();
        location.reload();
      }
    };
  </script>
</body>
</html>
