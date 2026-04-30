<?php
header("Content-Type: application/json");

// Configura os cabeçalhos CORS para permitir requisições do app Flutter
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/../config/bootstrap.php';

$collection = $client->bingo_db->sessions;

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['userId'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Usuário não autenticado.']);
        exit;
    }

    // GERA O ID CURTO E ÚNICO
    $shortId = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5);
    // Em produção, você adicionaria um loop aqui para garantir a unicidade:
    while ($collection->countDocuments(['shortId' => $shortId]) > 0) {
         $shortId = substr(str_shuffle('...'), 0, 5);
    }

    $result = $collection->insertOne([
        'userId' => new \MongoDB\BSON\ObjectId($data['userId']), 
        'shortId' => $shortId, // <-- SALVA O ID CURTO NO BANCO
        'sessionName' => $data['sessionName'] ?? 'Bingo Padrão',
        'round' => $data['round'] ?? 1,
        'prize' => $data['prize'] ?? 'Sem prêmio',
        'status' => 'active',
        'isProUser' => $data['isProUser'] ?? false,
        'drawnNumbers' => [],
        'winners' => [],
        'createdAt' => new \MongoDB\BSON\UTCDateTime()
    ]);

    echo json_encode([
        'success' => true,
        'sessionId' => (string)$result->getInsertedId(), // ID longo do Mongo
        'shortSessionId' => $shortId // <-- NOVO: ID curto para a URL
    ]);


} elseif ($method === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $session = $collection->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
        if ($session) {
            $session['_id'] = (string)$session['_id'];
            $session['createdAt'] = $session['createdAt']->toDateTime()->format('c'); // Formato ISO 8601
            echo json_encode($session);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Sessão não encontrada']);
        }
    } catch (\Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => 'ID da sessão inválido']);
    }
} elseif ($method === 'GET' && isset($_GET['shortId'])) {
    $shortId = $_GET['shortId'];
    try {
        $session = $collection->findOne(['shortId' => $shortId]);
        if ($session) {
            $session['_id'] = (string)$session['_id'];
            $session['createdAt'] = $session['createdAt']->toDateTime()->format('c'); // Formato ISO 8601
            echo json_encode($session);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Sessão não encontrada']);
        }
    } catch (\Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => 'ID da sessão inválido']);
    }
} else {
     http_response_code(400);
     echo json_encode(['error' => 'Requisição inválida']);
}