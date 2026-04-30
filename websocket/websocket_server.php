<?php
// websocket_server.php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class BingoServer implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        echo "Bingo Server iniciado.\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "Nova conexão: ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "Mensagem recebida: $msg\n";
        $data = json_decode($msg, true);
        // Valida e direciona a mensagem conforme a ação
        if(isset($data['action'])) {
            switch ($data['action']) {
                case 'new_draw':
                    // Encaminha a mensagem para todos os clientes conectados
                    $this->broadcast($msg, $from);
                    break;
                case 'clear_round':
                    // Envia a mensagem para todos para limpar a tela
                    $this->broadcast($msg);
                    break;
                default:
                    echo "Ação desconhecida: " . $data['action'] . "\n";
                    break;
            }
        }
    }

    // Função para enviar mensagem para todos os clientes (opcionalmente exceto o remetente)
    private function broadcast($msg, $exclude = null) {
        foreach ($this->clients as $client) {
            if ($client !== $exclude) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Conexão {$conn->resourceId} fechada.\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Erro: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Lê a porta do WebSocket a partir da variável de ambiente (padrão: 8080)
$wsPort = getenv('WS_PORT') ?: 8080;
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new BingoServer()
        )
    ),
    $wsPort
);

echo "Servidor WebSocket rodando na porta $wsPort\n";
$server->run();
