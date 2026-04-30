<?php
// submit_draw.php
require_once 'db.php';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['draw'])) {
    $draw = intval($_POST['draw']);
    if($draw < 1 || $draw > 75) {
        echo json_encode(['success' => false, 'message' => 'Número inválido!']);
        exit;
    }
    try {
        // Verifica se o número já foi sorteado nesta rodada
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bingo_draws WHERE round = (SELECT current_round FROM bingo_round LIMIT 1) AND draw = ?");
        $stmt->execute([$draw]);
        if($stmt->fetchColumn() > 0){
            echo json_encode(['success' => false, 'message' => 'Número já sorteado!']);
            exit;
        }
        // Insere o número sorteado
        $stmt = $pdo->prepare("INSERT INTO bingo_draws (round, draw, drawn_at) VALUES ((SELECT current_round FROM bingo_round LIMIT 1), ?, NOW())");
        $stmt->execute([$draw]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage()]);
    }
}
?>
