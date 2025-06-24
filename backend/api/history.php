<?php
header("Content-Type: application/json");

// Configura os cabeçalhos CORS para permitir requisições do app Flutter
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


require_once __DIR__ . '/../config/bootstrap.php';


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

if (!isset($_GET['userId'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID do usuário é obrigatório.']);
    exit;
}

$sessionsCollection = $client->bingo_db->sessions;

$userId = $_GET['userId'];
$sessions = $sessionsCollection->find(
    ['userId' => new \MongoDB\BSON\ObjectId($userId)],
    ['sort' => ['createdAt' => -1]] // Ordena pelas mais recentes
);

$result = [];
foreach ($sessions as $session) {
    $session['_id'] = (string)$session['_id'];
    $session['userId'] = (string)$session['userId'];
    $session['createdAt'] = $session['createdAt']->toDateTime()->format('c');
    $result[] = $session;
}

echo json_encode($result);