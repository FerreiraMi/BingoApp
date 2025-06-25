<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\BingoChat;

require __DIR__ . '/vendor/autoload.php';

echo "Iniciando servidor WebSocket...\n";
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new BingoChat()
        )
    ),
    8080,
    '0.0.0.0' // Ouve em todas as interfaces de rede
);

$server->run();