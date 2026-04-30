<?php
header("Content-Type: application/json");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/../config/bootstrap.php';

$collection = $client->bingo_db->display_tokens;

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($method === 'POST') {
    // Gera um código de display único de 6 caracteres
    $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    do {
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
    } while ($collection->countDocuments(['code' => $code, 'status' => 'waiting']) > 0);

    $collection->insertOne([
        'code'           => $code,
        'status'         => 'waiting',
        'sessionShortId' => null,
        'createdAt'      => new \MongoDB\BSON\UTCDateTime(),
    ]);

    echo json_encode(['success' => true, 'code' => $code]);

} elseif ($method === 'GET' && isset($_GET['code'])) {
    // Navegador verifica se o APP já vinculou uma sessão ao display token
    $code  = strtoupper(trim($_GET['code']));
    $token = $collection->findOne(['code' => $code]);

    if ($token) {
        echo json_encode([
            'status'         => $token['status'],
            'sessionShortId' => $token['sessionShortId'],
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Código de display não encontrado.']);
    }

} elseif ($method === 'PUT') {
    // APP vincula a sua sessão ativa ao display token gerado pelo navegador
    $data           = json_decode(file_get_contents('php://input'), true);
    $code           = strtoupper(trim($data['code'] ?? ''));
    $sessionShortId = strtoupper(trim($data['sessionShortId'] ?? ''));

    if (empty($code) || empty($sessionShortId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Dados incompletos.']);
        exit;
    }

    // Valida que a sessão existe e está ativa
    $session = $client->bingo_db->sessions->findOne([
        'shortId' => $sessionShortId,
        'status'  => 'active',
    ]);

    if (!$session) {
        http_response_code(404);
        echo json_encode(['error' => 'Sessão ativa não encontrada.']);
        exit;
    }

    $result = $collection->updateOne(
        ['code' => $code, 'status' => 'waiting'],
        ['$set' => ['status' => 'linked', 'sessionShortId' => $sessionShortId]]
    );

    if ($result->getModifiedCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Código de display não encontrado ou já vinculado.']);
    }

} else {
    http_response_code(400);
    echo json_encode(['error' => 'Requisição inválida']);
}
