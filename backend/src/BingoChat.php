<?php
namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use MongoDB\Client;

class BingoChat implements MessageComponentInterface {
    protected $clients;
    private $db;
    
    // Mapeamento: resourceId => ['shortId' => ..., 'viewerName' => ...]
    // Unificamos 'sessionId' para ser sempre o 'shortId' para rastreamento de conexão.
    private $connectionData; 
    
    // Mapeamento: shortId => [ resourceId => 'viewerName', ... ]
    private $sessionViewers;

    public function __construct() {
        require __DIR__ . '/../config/bootstrap.php';
        
        $this->clients = new \SplObjectStorage;
        $this->connectionData = [];
        $this->sessionViewers = [];

        $this->db = $client->selectDatabase('bingo_db');
        $this->logMessage("Servidor WebSocket iniciado e conectado ao MongoDB.", null);
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        $this->connectionData[$conn->resourceId] = ['shortId' => null, 'viewerName' => null];
        $this->logMessage("Nova conexão!", $conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        $resourceId = $from->resourceId;
        
        // Roteamento de mensagens baseado no 'type'
        switch ($data['type'] ?? '') {
            case 'subscribe':
                $shortId = $data['sessionId'];
                $this->connectionData[$resourceId]['shortId'] = $shortId;
                $this->broadcastViewerList($shortId);
                $this->logMessage("Página web inscrita na sessão {$shortId}", $from);
                break;

            case 'viewer_joined':
                $shortId = $data['sessionId'];
                $viewerName = $data['viewerName'];
                $this->connectionData[$resourceId]['shortId'] = $shortId;
                $this->connectionData[$resourceId]['viewerName'] = $viewerName;

                if (!isset($this->sessionViewers[$shortId])) {
                    $this->sessionViewers[$shortId] = [];
                }
                $this->sessionViewers[$shortId][$resourceId] = $viewerName;
                $this->logMessage("Espectador '{$viewerName}' entrou na sessão {$shortId}", $from);
                $this->broadcastViewerList($shortId);
                break;
            
            case 'redirect':
                // A lógica de redirect precisa de um mapeamento entre shortId e os clientes.
                // Esta lógica funcionará se os clientes que precisam ser redirecionados
                // também se inscreverem com um 'subscribe'.
                $targetShortId = $data['targetSessionId']; // Assumindo que o app manda o shortId
                $newUrl = $data['newUrl'];
                $this->logMessage("Comando de redirect para sessão {$targetShortId}", $from);
                $this->broadcastToSession($targetShortId, json_encode($data));
                break;
                
            case 'bingo_called':
                $longSessionId = $data['sessionId'];
                $winners = $data['winners'] ?? [];

                // Buscamos o shortId a partir do longId para saber para quem transmitir
                $sessionDoc = $this->db->sessions->findOne(['_id' => new \MongoDB\BSON\ObjectId($longSessionId)], ['projection' => ['shortId' => 1]]);
                if ($sessionDoc) {
                    $shortId = $sessionDoc['shortId'];
                    if (!empty($winners)) {
                        $this->db->sessions->updateOne(['_id' => new \MongoDB\BSON\ObjectId($longSessionId)], ['$set' => ['winners' => $winners]]);
                    }
                    $this->logMessage("Bingo chamado para sessão {$shortId} por: " . implode(', ', $winners), $from);
                    $this->broadcastToSession($shortId, json_encode(['type' => 'bingo_called', 'winners' => $winners]));
                }
                break;

            case 'new_number': // O default case, sem 'type' explícito na mensagem original
                $longSessionId = $data['sessionId'];
                $number = (int)$data['number'];

                // Novamente, buscamos o shortId para transmitir
                $sessionDoc = $this->db->sessions->findOne(['_id' => new \MongoDB\BSON\ObjectId($longSessionId)], ['projection' => ['shortId' => 1]]);
                if ($sessionDoc) {
                    $shortId = $sessionDoc['shortId'];
                    try {
                        $this->db->sessions->updateOne(['_id' => new \MongoDB\BSON\ObjectId($longSessionId)], ['$addToSet' => ['drawnNumbers' => $number]]);
                        $this->logMessage("Número {$number} recebido para sessão {$shortId}", $from);
                        $this->broadcastToSession($shortId, json_encode(['type' => 'new_number', 'number' => $number]));
                    } catch (\Exception $e) {
                        $this->logMessage("Erro ao salvar no MongoDB: " . $e->getMessage(), $from);
                    }
                }
                break;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $resourceId = $conn->resourceId;
        if (isset($this->connectionData[$resourceId])) {
            $shortId = $this->connectionData[$resourceId]['shortId'];
            $viewerName = $this->connectionData[$resourceId]['viewerName'];
            
            if ($shortId && $viewerName) {
                unset($this->sessionViewers[$shortId][$resourceId]);
                $this->logMessage("Espectador '{$viewerName}' saiu da sessão {$shortId}", $conn);
                $this->broadcastViewerList($shortId);
            }
            
            unset($this->connectionData[$resourceId]);
        }
        $this->clients->detach($conn);
        $this->logMessage("Conexão desconectada.", $conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->logMessage("Ocorreu um erro: {$e->getMessage()}", $conn);
        $conn->close();
    }

    private function broadcastToSession($shortId, $payload) {
        foreach ($this->clients as $client) {
            if (isset($this->connectionData[$client->resourceId]) && $this->connectionData[$client->resourceId]['shortId'] === $shortId) {
                $client->send($payload);
            }
        }
    }

    private function broadcastViewerList($shortId) {
        $viewers = isset($this->sessionViewers[$shortId]) ? array_values($this->sessionViewers[$shortId]) : [];
        $payload = json_encode(['type' => 'viewer_list_update', 'viewers' => $viewers]);
        $this->broadcastToSession($shortId, $payload);
    }

    private function logMessage($message, ?ConnectionInterface $conn) {
        $ip = $conn ? $conn->remoteAddress : 'SERVER';
        echo date("Y-m-d H:i:s") . " [{$ip}] " . $message . "\n";
    }
}