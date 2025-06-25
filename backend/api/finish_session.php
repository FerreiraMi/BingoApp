<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Trata requisição OPTIONS (pré-flight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/bootstrap.php';

$collection = $client->bingo_db->sessions;

$data = json_decode(file_get_contents('php://input'), true);
error_log(print_r($data, true)); // Log para depuração

if (empty($data['sessionId'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID da sessão é obrigatório.']);
    exit;
}

try {
    $result = $collection->updateOne(
        ['_id' => new \MongoDB\BSON\ObjectId($data['sessionId'])],
        ['$set' => ['status' => 'finished']] // Atualiza o status para 'finished'
    );

    if ($result->getModifiedCount() === 1) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Sessão encerrada com sucesso.']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Sessão não encontrada ou já encerrada.']);
    }

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro no servidor.']);
}
?>