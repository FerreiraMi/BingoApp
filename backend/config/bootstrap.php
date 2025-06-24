<?php
// config/bootstrap.php


// Carrega todas as dependências do Composer.
// Usamos __DIR__ para criar um caminho absoluto robusto, não importa de onde este arquivo seja incluído.
require_once __DIR__ . '/../vendor/autoload.php';

// Pega a string de conexão do banco de dados da variável de ambiente.
$mongoDsn = getenv('MONGO_DSN');

// Ponto único de falha: se a variável não estiver definida, a aplicação para.
if ($mongoDsn === false) {
    http_response_code(500);
    // Para APIs, retornamos JSON. Para CLI (WebSocket), isso aparecerá no console.
    echo json_encode([
        'success' => false, 
        'error' => 'Configuração do servidor incompleta: A variável de ambiente MONGO_DSN não está definida.'
    ]);
    exit; // Interrompe a execução imediatamente.
}

try {
    // Cria a instância do cliente MongoDB.
    // Esta variável $client estará disponível em qualquer script que inclua este arquivo.
    $client = new MongoDB\Client($mongoDsn);
} catch (\Exception $e) {
    // Se a conexão com o banco falhar por qualquer motivo (ex: banco offline).
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Não foi possível conectar ao banco de dados.',
        'details' => $e->getMessage() // Opcional: útil para depuração
    ]);
    exit;
}