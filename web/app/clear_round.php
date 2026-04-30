<?php
// clear_round.php
require_once 'db.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $winners = isset($_POST['winners']) ? trim($_POST['winners']) : '';
    try {
        $pdo->beginTransaction();
        // Obtém a rodada atual
        $stmt = $pdo->query("SELECT current_round FROM bingo_round LIMIT 1");
        $currentRound = $stmt->fetchColumn();

        // Registra os nomes dos ganhadores para a rodada atual, se informados
        if($winners != ''){
            $insertStmt = $pdo->prepare("INSERT INTO bingo_winners (round, winners, recorded_at) VALUES (?, ?, NOW())");
            $insertStmt->execute([$currentRound, $winners]);
        }

        // Apaga os números sorteados da rodada atual
        $pdo->prepare("DELETE FROM bingo_draws WHERE round = ?")->execute([$currentRound]);

        // Incrementa a rodada (para iniciar uma nova)
        $pdo->prepare("UPDATE bingo_round SET current_round = current_round + 1")->execute();

        $pdo->commit();
        echo "Rodada finalizada com sucesso.";
    } catch(PDOException $e) {
        $pdo->rollBack();
        echo "Erro: " . $e->getMessage();
    }
}
?>
