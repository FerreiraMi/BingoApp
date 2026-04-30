<?php
header("Content-Type: application/json");

// Configura os cabeçalhos CORS para permitir requisições do app Flutter
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


require_once __DIR__ . '/../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


$usersCollection = $client->bingo_db->users;

// Pega os dados brutos do corpo da requisição e decodifica o JSON
$data = json_decode(file_get_contents('php://input'), true);

// 1. Validação de Entrada
// Verifica se os campos essenciais foram enviados
if (empty($data['email']) || empty($data['password'])) {
    http_response_code(400); // 400 Bad Request
    echo json_encode(['success' => false, 'error' => 'Email e senha são obrigatórios.']);
    exit;
}

// Valida o formato do email
if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); // 400 Bad Request
    echo json_encode(['success' => false, 'error' => 'Formato de email inválido.']);
    exit;
}

// Valida a força da senha (ex: mínimo de 6 caracteres)
if (strlen($data['password']) < 6) {
    http_response_code(400); // 400 Bad Request
    echo json_encode(['success' => false, 'error' => 'A senha deve ter no mínimo 6 caracteres.']);
    exit;
}

// 2. Verifica se o usuário já existe
try {
    // Procura por um documento na coleção 'users' onde o campo 'email' corresponda ao email enviado
    $existingUser = $usersCollection->findOne(['email' => $data['email']]);

    if ($existingUser) {
        // Se encontrar um usuário, retorna um erro de conflito
        http_response_code(409); // 409 Conflict
        echo json_encode(['success' => false, 'error' => 'Este email já está cadastrado.']);
        exit;
    }
} catch (\Exception $e) {
    // Em caso de erro na consulta ao banco
    http_response_code(500); // 500 Internal Server Error
    echo json_encode(['success' => false, 'error' => 'Erro ao consultar o banco de dados.']);
    exit;
}

// 3. Cria um Hash Seguro da Senha
// Esta é a maneira correta e segura de armazenar senhas.
// A função `password_hash` usa um algoritmo forte e adiciona um "sal" aleatório.
$hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

// 4. Insere o novo usuário no banco de dados
try {
    $result = $usersCollection->insertOne([
        'email' => $data['email'],
        'password' => $hashedPassword,
        'createdAt' => new \MongoDB\BSON\UTCDateTime()
    ]);

    // Verifica se a inserção foi bem-sucedida
    if ($result->getInsertedCount() === 1) {
        // Retorna sucesso
        http_response_code(201); // 201 Created
        echo json_encode(['success' => true, 'message' => 'Usuário criado com sucesso.']);
    } else {
        throw new Exception("Falha ao inserir o usuário.");
    }
} catch (\Exception $e) {
    // Em caso de erro na inserção
    http_response_code(500); // 500 Internal Server Error
    echo json_encode(['success' => false, 'error' => 'Não foi possível criar o usuário. Tente novamente.']);
    exit;
}

?>