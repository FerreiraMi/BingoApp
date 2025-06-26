<?php
namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use MongoDB\Client;

class BingoChat implements MessageComponentInterface {
    protected $clients;
    private $db;
    private $sessions;
    private $connectionData;
    private $sessionViewers;


    public function __construct() {

        require __DIR__ . '/../config/bootstrap.php';
        
        $this->clients = new \SplObjectStorage;
        $this->sessions = [];
         $this->connectionData = [];
        $this->sessionViewers = [];

        
    
       // A variável $client é criada pelo arquivo bootstrap.php
        // Nós a usamos para selecionar o banco de dados.
        $this->db = $client->selectDatabase('bingo_db');

        echo "Servidor WebSocket iniciado e conectado ao MongoDB.\n";
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $this->connectionData[$conn->resourceId] = ['sessionId' => null, 'viewerName' => null];
        echo "Nova conexão! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        $resourceId = $from->resourceId;
        
        // Tipo: Inscrição da página web
        if (isset($data['type']) && $data['type'] === 'subscribe') {
            $shortId = $data['sessionId'];
            $this->connectionData[$resourceId]['sessionId'] = $shortId;
            $this->broadcastViewerList($shortId); // Envia a lista atual de espectadores para a nova página web conectada
            echo "Página web ({$resourceId}) inscrita na sessão {$shortId}\n";
            return;
        }
        
        // Tipo: Novo espectador do app se juntou
        if (isset($data['type']) && $data['type'] === 'viewer_joined') {
            $shortId = $data['sessionId'];
            $viewerName = $data['viewerName'];

            $this->connectionData[$resourceId]['sessionId'] = $shortId;
            $this->connectionData[$resourceId]['viewerName'] = $viewerName;

            // Adiciona o espectador à lista da sessão
            if (!isset($this->sessionViewers[$shortId])) {
                $this->sessionViewers[$shortId] = [];
            }
            $this->sessionViewers[$shortId][$resourceId] = $viewerName;

            echo "Espectador '{$viewerName}' ({$resourceId}) entrou na sessão {$shortId}\n";
            $this->broadcastViewerList($shortId); // Notifica todos sobre a mudança na lista
            return;
        }


        // Mensagem de redirecionamento enviada pelo app Flutter
        if (isset($data['type']) && $data['type'] === 'redirect') {
            $targetSessionId = $data['targetSessionId'];
            $newUrl = $data['newUrl'];
            
            echo "Comando de redirect recebido para a sessão {$targetSessionId}\n";
            
            // Transmite o comando de redirect apenas para os clientes da sessão antiga
            foreach ($this->clients as $client) {
                if (isset($this->sessions[$client->resourceId]) && $this->sessions[$client->resourceId] === $targetSessionId) {
                    $client->send(json_encode([
                        'type' => 'redirect',
                        'targetSessionId' => $targetSessionId,
                        'newUrl' => $newUrl
                    ]));
                }
            }
            return;
        }

        if (isset($data['type']) && $data['type'] === 'bingo_called') {
            $sessionId = $data['sessionId'];
            $winners = $data['winners'] ?? [];

            if (!empty($winners)) {
                $this->db->sessions->updateOne(
                    ['_id' => new \MongoDB\BSON\ObjectId($sessionId)],
                    ['$set' => ['winners' => $winners]]
                );
            }

            echo "Bingo chamado para a sessão {$sessionId} por: " . implode(', ', $winners) . "!\n";

            // Transmite a mensagem de bingo para todos os clientes daquela sessão
            foreach ($this->clients as $client) {
                if (isset($this->sessions[$client->resourceId]) && $this->sessions[$client->resourceId] === $sessionId) {
                    $client->send(json_encode(['type' => 'bingo_called', 'winners' => $winners]));
                }
            }
            return; // Encerra o processamento para esta mensagem
        }

        // Mensagem de um cliente web se inscrevendo em uma sessão
        if (isset($data['type']) && $data['type'] === 'subscribe' && isset($data['sessionId'])) {
            $this->sessions[$from->resourceId] = $data['sessionId'];
            echo "Conexão {$from->resourceId} inscrita na sessão {$data['sessionId']}\n";
            return;
        }

        // Mensagem do app Flutter com novo número
        if (isset($data['sessionId']) && isset($data['number'])) {
            $sessionId = $data['sessionId'];
            $number = (int)$data['number'];

            try {
                $this->db->sessions->updateOne(
                    ['_id' => new \MongoDB\BSON\ObjectId($sessionId)],
                    ['$addToSet' => ['drawnNumbers' => $number]]
                );
            } catch (\Exception $e) {
                 echo "Erro ao salvar no MongoDB: " . $e->getMessage() . "\n";
                 return;
            }
            
            echo "Número {$number} recebido para a sessão {$sessionId}. Transmitindo...\n";

            // Envia a atualização apenas para os clientes inscritos na sessão correta
            foreach ($this->clients as $client) {
                if (isset($this->sessions[$client->resourceId]) && $this->sessions[$client->resourceId] === $sessionId) {
                    $client->send(json_encode(['type' => 'new_number', 'number' => $number]));
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        unset($this->sessions[$conn->resourceId]);
        $this->clients->detach($conn);
        echo "Conexão {$conn->resourceId} foi desconectada.\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Ocorreu um erro: {$e->getMessage()}\n";
        $conn->close();
    }

    // Nova função auxiliar para transmitir a lista de espectadores
    private function broadcastViewerList($shortId) {
        $viewers = isset($this->sessionViewers[$shortId]) ? array_values($this->sessionViewers[$shortId]) : [];
        $payload = json_encode(['type' => 'viewer_list_update', 'viewers' => $viewers]);

        foreach ($this->clients as $client) {
            if (isset($this->connectionData[$client->resourceId]) && $this->connectionData[$client->resourceId]['sessionId'] === $shortId) {
                $client->send($payload);
            }
        }
    }
}