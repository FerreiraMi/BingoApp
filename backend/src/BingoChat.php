<?php
namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use MongoDB\Client;
use MongoDB\BSON\ObjectId; // Importe a classe ObjectId para facilitar

class BingoChat implements MessageComponentInterface {
    protected $clients;
    private $db;
    
    // Mapeamento: resourceId => ['shortId' => ..., 'viewerName' => ..., 'isOperator' => ...]
    private $connectionData; 
    
    // Mapeamento: shortId => [ resourceId => 'viewerName', ... ]
    private $sessionViewers;

    // <-- MUDANÇA: Adicionado para otimizar buscas no DB
    // Mapeamento: shortId <=> _id
    private $sessionMap;

    public function __construct() {
        require __DIR__ . '/../config/bootstrap.php';
        
        $this->clients = new \SplObjectStorage;
        $this->connectionData = [];
        $this->sessionViewers = [];
        $this->sessionMap = []; // <-- MUDANÇA: Inicializa o novo array

        $this->db = $client->selectDatabase('bingo_db');
        $this->logMessage("Servidor WebSocket iniciado.", null);
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        // Inicializa com todos os campos possíveis para evitar "undefined index"
        $this->connectionData[$conn->resourceId] = ['shortId' => null, 'viewerName' => null, 'isOperator' => false];
        $this->logMessage("Nova conexão!", $conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if (!$data) { $this->logMessage("Mensagem JSON inválida recebida: $msg", $from); return; }
        
        $resourceId = $from->resourceId;
        $type = $data['type'] ?? 'new_number';

        switch ($type) {
            // <-- MUDANÇA: Novo case para autenticação
            case 'authenticate_operator':
                $longSessionId = $data['sessionId'] ?? null;
                $userId = $data['userId'] ?? null;
                
                if (!$longSessionId || !$userId) {
                    $this->logMessage("ERRO: Tentativa de autenticação com dados incompletos.", $from);
                    return;
                }

                $sessionDoc = $this->db->sessions->findOne(
                    ['_id' => new ObjectId($longSessionId), 'userId' => new ObjectId($userId)],
                    ['projection' => ['shortId' => 1]]
                );

                if ($sessionDoc) {
                    $shortId = $sessionDoc['shortId'];
                    $this->connectionData[$resourceId]['shortId'] = $shortId;
                    $this->connectionData[$resourceId]['isOperator'] = true; // Marca como operador
                    $this->sessionMap[$shortId] = new ObjectId($longSessionId); // Adiciona ao mapa para otimização
                    $this->connectionData[$resourceId]['userId'] = $userId; // <-- MUDANÇA: Armazena o ID do operador

                    $this->logMessage("Operador autenticado com sucesso para a sessão {$shortId}", $from);
                } else {
                    $this->logMessage("ERRO: Falha na autenticação do operador para a sessão {$longSessionId}", $from);
                    $from->close();
                }
                break;

            case 'subscribe':
                $shortId = $data['sessionId'];
                $this->connectionData[$resourceId]['shortId'] = $shortId;
                $this->broadcastViewerList($shortId);
                $this->logMessage("Página web inscrita na sessão {$shortId}", $from);
                break;

            case 'viewer_joined':
                // Sua lógica original aqui está correta e permanece a mesma
                $shortId = $data['sessionId'];
                $viewerName = $data['viewerName'];
                $this->connectionData[$resourceId]['shortId'] = $shortId;
                $this->connectionData[$resourceId]['viewerName'] = $viewerName;
                if (!isset($this->sessionViewers[$shortId])) { $this->sessionViewers[$shortId] = []; }
                $this->sessionViewers[$shortId][$resourceId] = $viewerName;
                $this->logMessage("Espectador '{$viewerName}' entrou na sessão {$shortId}", $from);
                $this->broadcastViewerList($shortId);
                break;
            
            case 'redirect':
                // Sua lógica original aqui está correta e permanece a mesma
                $targetShortId = $data['targetSessionId'];
                $newUrl = $data['newUrl'];
                $this->logMessage("Comando de redirect para sessão {$targetShortId}", $from);
                $this->broadcastToSession($targetShortId, json_encode($data));
                break;
                
            case 'bingo_called':
                // Sua lógica original aqui está correta e permanece a mesma
                $longSessionId = $data['sessionId'];
                $winners = $data['winners'] ?? [];
                $shortId = $this->getShortIdFromLongId($longSessionId);
                if ($shortId) {
                    if (!empty($winners)) {
                        $this->db->sessions->updateOne(['_id' => new ObjectId($longSessionId)], ['$set' => ['winners' => $winners]]);
                    }
                    $this->logMessage("Bingo chamado para sessão {$shortId} por: " . implode(', ', $winners), $from);
                    $this->broadcastToSession($shortId, json_encode(['type' => 'bingo_called', 'winners' => $winners]));
                }
                break;

            case 'new_number':
                // <-- MUDANÇA: Adicionada verificação de permissão
                if (!($this->connectionData[$resourceId]['isOperator'] ?? false)) {
                    $this->logMessage("ERRO: Tentativa de sorteio por conexão não autorizada.", $from);
                    return; // Aborta a operação
                }
                
                $longSessionId = $data['sessionId'];
                $number = (int)$data['number'];
                $shortId = $this->getShortIdFromLongId($longSessionId);

                if ($shortId) {
                    $this->db->sessions->updateOne(['_id' => new ObjectId($longSessionId)], ['$addToSet' => ['drawnNumbers' => $number]]);
                    $this->logMessage("Número {$number} recebido para sessão {$shortId}", $from);
                    $this->broadcastToSession($shortId, json_encode(['type' => 'new_number', 'number' => $number]));
                } else {
                    $this->logMessage("ERRO: Tentativa de sortear número para sessão desconhecida. LongId: {$longSessionId}", $from);
                }
                break;

            default:
                $this->logMessage("Mensagem com tipo desconhecido recebida: " . ($data['type'] ?? 'N/A'), $from);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $resourceId = $conn->resourceId;
        if (isset($this->connectionData[$resourceId])) {
            $shortId = $this->connectionData[$resourceId]['shortId'];
            $viewerName = $this->connectionData[$resourceId]['viewerName'];
            $isOperator = $this->connectionData[$resourceId]['isOperator']; // <-- MUDANÇA: Pega o status de operador
            
            if ($shortId && $viewerName) {
                unset($this->sessionViewers[$shortId][$resourceId]);
                $this->logMessage("Espectador '{$viewerName}' saiu da sessão {$shortId}", $conn);
                $this->broadcastViewerList($shortId);
            }
            
            // <-- MUDANÇA: Adiciona log para a desconexão do operador
            if ($shortId && $isOperator) {
                $this->logMessage("Operador da sessão {$shortId} desconectou.", $conn);
                // Opcional: Futuramente, você pode querer notificar a página web sobre isso.
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
            if (($this->connectionData[$client->resourceId]['shortId'] ?? null) === $shortId) {
                $client->send($payload);
            }
        }
    }

    private function broadcastViewerList($shortId) {
        $viewers = isset($this->sessionViewers[$shortId]) ? array_values($this->sessionViewers[$shortId]) : [];
        $payload = json_encode(['type' => 'viewer_list_update', 'viewers' => $viewers]);
        $this->broadcastToSession($shortId, $payload);
    }

    // <-- MUDANÇA: Função auxiliar para otimizar buscas no DB
    private function getShortIdFromLongId($longSessionId) {
        foreach ($this->sessionMap as $short => $long) {
            if ((string)$long === (string)$longSessionId) {
                return $short;
            }
        }
        $sessionDoc = $this->db->sessions->findOne(['_id' => new ObjectId($longSessionId)], ['projection' => ['shortId' => 1]]);
        if ($sessionDoc) {
            $this->sessionMap[$sessionDoc['shortId']] = new ObjectId($longSessionId);
            return $sessionDoc['shortId'];
        }
        return null;
    }
    
    // <-- MUDANÇA: Corrigido o bug do IP
    private function logMessage($message, ?ConnectionInterface $conn) {
        // Inicializa as variáveis de log
        $ip = 'SERVER';
        $port = '-';
        $user = 'SYSTEM';

        if ($conn) {
            // Pega o IP real que foi definido no server.php
            $ip = $conn->realIp ?? $conn->remoteAddress;
            // Se o remoteAddress contiver uma porta, extrai-a
            if (strpos($conn->remoteAddress, ':') !== false) {
                $parts = explode(':', $conn->remoteAddress);
                $port = end($parts);
            }

            // Verifica se há um ID de operador ou nome de espectador associado à conexão
            $resourceId = $conn->resourceId;
            if (isset($this->connectionData[$resourceId])) {
                $userId = $this->connectionData[$resourceId]['userId'];
                $viewerName = $this->connectionData[$resourceId]['viewerName'];

                if ($userId) {
                    // Se for um operador, mostra o ID dele
                    $user = "Operator({$userId})";
                } elseif ($viewerName) {
                    // Se for um espectador, mostra o nome dele
                    $user = "Viewer({$viewerName})";
                } else {
                    // Se for uma conexão ainda não identificada (ex: página web)
                    $user = "Guest";
                }
            }
        }
        
        // Monta a string de log final
        echo date("Y-m-d H:i:s") . " [{$ip}:{$port}] [User: {$user}] " . $message . "\n";
    }
}