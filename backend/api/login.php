<?php
require_once __DIR__ . '/../config/bootstrap.php';
// Define que a resposta será em formato JSON
header("Content-Type: application/json");

// Configura os cabeçalhos CORS para permitir requisições do app Flutter
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/../config/bootstrap.php';

// Responde com sucesso a requisições OPTIONS (pre-flight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Inclui o autoload do Composer
require '../vendor/autoload.php';
$usersCollection = $client->bingo_db->users;

// Pega os dados enviados pelo app
$data = json_decode(file_get_contents('php://input'), true);

// 1. Validação de Entrada Básica
if (empty($data['email']) || empty($data['password'])) {
    http_response_code(400); // 400 Bad Request
    echo json_encode(['success' => false, 'error' => 'Email e senha são obrigatórios.']);
    exit;
}

// 2. Busca o usuário pelo email
try {
    $user = $usersCollection->findOne(['email' => $data['email']]);
} catch (\Exception $e) {
    http_response_code(500); // 500 Internal Server Error
    echo json_encode(['success' => false, 'error' => 'Erro no servidor ao buscar usuário.']);
    exit;
}

// 3. Verificação de Credenciais
// A verificação acontece em duas etapas, combinadas em uma única condição:
// a) O usuário foi encontrado no banco de dados (`$user` não é nulo)?
// b) A senha fornecida ($data['password']) corresponde ao hash armazenado no banco ($user['password'])?
//
// A função `password_verify` compara a senha em texto plano com o hash de forma segura.
// É crucial usar esta função em vez de uma comparação simples (ex: md5($senha) == $hash).
//
// Por segurança, a mensagem de erro é genérica para não informar a um atacante
// se o erro foi no email ou na senha.

if (!$user || !password_verify($data['password'], $user['password'])) {
    http_response_code(401); // 401 Unauthorized - O código correto para credenciais inválidas
    echo json_encode(['success' => false, 'error' => 'Email ou senha inválidos.']);
    exit;
}

// 4. Login bem-sucedido
// Se o código chegou até aqui, o usuário foi autenticado com sucesso.
// Retornamos os dados necessários para o app Flutter manter o usuário logado.

http_response_code(200); // 200 OK
echo json_encode([
    'success' => true,
    'userId' => (string)$user['_id'], // Converte o ObjectId do MongoDB para uma string
    'email' => $user['email']
]);

?>