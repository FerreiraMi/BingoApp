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

    /**
     * @param \MongoDB\Database|null $database Banco injetável; se null usa o bootstrap de produção.
     */
    public function __construct($database = null) {
        $this->clients = new \SplObjectStorage;
        $this->connectionData = [];
        $this->sessionViewers = [];
        $this->sessionMap = [];

        if ($database !== null) {
            $this->db = $database;
        } else {
            require __DIR__ . '/../config/bootstrap.php';
            $this->db = $client->selectDatabase('bingo_db');
        }

        $this->logMessage("Servidor WebSocket iniciado.", null);
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        // Inicializa com todos os campos possíveis para evitar "undefined index"
        $this->connectionData[$conn->resourceId] = ['shortId' => null, "sessionId", 'viewerName' => null, 'isOperator' => false];
        $this->logMessage("Nova conexão!", $conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        if (!$data) { $this->logMessage("Mensagem JSON inválida recebida: $msg", $from); return; }
        
        $resourceId = $from->resourceId;
        $type = $data['type'] ?? 'new_number';

        try {
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
                        $this->connectionData[$resourceId]['isOperator'] = true;
                        $this->connectionData[$resourceId]['sessionId'] = (string)$longSessionId; // para broadcastToSession
                        $this->sessionMap[$shortId] = new ObjectId($longSessionId);
                        $this->connectionData[$resourceId]['userId'] = $userId;

                        $this->logMessage("Operador autenticado com sucesso para a sessão {$longSessionId}/{$shortId}", $from);
                    } else {
                        $this->logMessage("ERRO: Falha na autenticação do operador para a sessão {$longSessionId}", $from);
                        $from->close();
                    }
                    break;

                case 'subscribe':
                    $shortId   = $data['shortId'];
                    $sessionId = $data['sessionId'];
                    $this->connectionData[$resourceId]['shortId'] = $shortId;
                    $this->connectionData[$resourceId]['sessionId'] = $sessionId;
                    $this->broadcastViewerList($sessionId);
                    $this->logMessage("Página web inscrita na sessão {$sessionId}/{$shortId}", $from);
                    break;

                case 'viewer_joined':
                    $shortId    = $data['shortId'];
                    $sessionId  = $this->getLongIdFromShortId($shortId);
                    $viewerName = $data['viewerName'];

                    $this->connectionData[$resourceId]['shortId']    = $shortId;
                    $this->connectionData[$resourceId]['sessionId']  = $sessionId;
                    $this->connectionData[$resourceId]['viewerName'] = $viewerName; // necessário para onClose remover corretamente

                    if (!isset($this->sessionViewers[$sessionId])) { $this->sessionViewers[$sessionId] = []; }
                    $this->sessionViewers[$sessionId][$resourceId] = $viewerName;
                    $this->logMessage("Espectador '{$viewerName}' entrou na sessão {$sessionId}/{$shortId}", $from);
                    $this->broadcastViewerList($sessionId);
                    break;
                
                case 'redirect':
                    // Sua lógica original aqui está correta e permanece a mesma
                    $targetShortId = $data['targetSessionId'];
                    $newUrl = $data['newUrl'];
                    $this->logMessage("Comando de redirect para sessão $newUrl {$targetShortId}", $from);
                    $this->broadcastToSession($targetShortId, json_encode($data));
                    break;
                    
                case 'session_settings':
                    if (!($this->connectionData[$resourceId]['isOperator'] ?? false)) {
                        $this->logMessage("ERRO: session_settings de conexão não autorizada.", $from);
                        return;
                    }
                    $longSessionId = $this->connectionData[$resourceId]['sessionId'] ?? null;
                    if ($longSessionId) {
                        $this->logMessage("Configurações da sessão atualizadas para {$longSessionId}.", $from);
                        $this->broadcastToSession($longSessionId, json_encode($data));
                    }
                    break;

                case 'bingo_called':
                    $longSessionId = $data['sessionId'];
                    $winners = $data['winners'] ?? [];
                    if (!empty($winners)) {
                        try {
                            $this->db->sessions->updateOne(
                                ['_id' => new ObjectId($longSessionId)],
                                ['$set' => ['winners' => $winners]]
                            );
                        } catch (\Exception $dbEx) {
                            $this->logMessage("AVISO: Não foi possível salvar ganhadores no DB: {$dbEx->getMessage()}", $from);
                        }
                    }
                    $shortId = $this->getShortIdFromLongId($longSessionId) ?? '?';
                    $this->logMessage("Bingo chamado para sessão {$longSessionId}/{$shortId} por: " . implode(', ', $winners), $from);
                    // Broadcast direto pelo longId sem depender do shortId
                    $this->broadcastToSession($longSessionId, json_encode(['type' => 'bingo_called', 'winners' => $winners]));
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
                        $this->logMessage("Número {$number} recebido para sessão {$longSessionId}/{$shortId}", $from);
                        $this->broadcastToSession($longSessionId, json_encode(['type' => 'new_number', 'number' => $number]));
                    } else {
                        $this->logMessage("ERRO: Tentativa de sortear número para sessão desconhecida. LongId: {$longSessionId}", $from);
                    }
                    break;

                default:
                    $this->logMessage("Mensagem com tipo desconhecido recebida: " . ($data['type'] ?? 'N/A'), $from);
            }
        } catch (\Exception $e) {
            $this->logMessage("ERRO: {$e->getMessage()}", $from);
        }

       
    }

    public function onClose(ConnectionInterface $conn) {
        $resourceId = $conn->resourceId;
        if (isset($this->connectionData[$resourceId])) {
            $sessionId = $this->connectionData[$resourceId]['sessionId'];
            $viewerName = $this->connectionData[$resourceId]['viewerName'];
            $isOperator = $this->connectionData[$resourceId]['isOperator']; // <-- MUDANÇA: Pega o status de operador
            
            if ($sessionId && $viewerName) {
                unset($this->sessionViewers[$sessionId][$resourceId]);
                $this->logMessage("Espectador '{$viewerName}' saiu da sessão {$sessionId}", $conn);
                $this->broadcastViewerList($sessionId);
            }
            
            // <-- MUDANÇA: Adiciona log para a desconexão do operador
            if ($sessionId && $isOperator) {
                $this->logMessage("Operador da sessão {$sessionId} desconectou.", null);
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

    private function broadcastToSession($sessionId, $payload) {
        foreach ($this->clients as $client) {
            $resource = $this->connectionData[$client->resourceId];
            if (($resource['sessionId'] ?? null) === $sessionId) {
                
                $client->send($payload);
                $this->logMessage("Send to ".$resource['sessionId']."/". $resource['shortId']. " payload: $payload", null);
            }
        }
    }

    private function broadcastViewerList($sessionId) {
        $viewers = isset($this->sessionViewers[$sessionId]) ? array_values($this->sessionViewers[$sessionId]) : [];
        $payload = json_encode(['type' => 'viewer_list_update', 'viewers' => $viewers]);
        $this->broadcastToSession($sessionId, $payload);
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
    
    private function getLongIdFromShortId($shortId) {
        foreach ($this->sessionMap as $short => $long) {
            if ((string)$short === (string)$shortId) {
                return (string)$long;   // sempre string para broadcastToSession funcionar
            }
        }
        $sessionDoc = $this->db->sessions->findOne(['shortId' => $shortId], ['projection' => ['_id' => 1]]);
        if ($sessionDoc) {
            $id = (string) $sessionDoc['_id']->__toString();
            print_r($id); // <-- MUDANÇA: Log para depuração
            echo "ID longo encontrado: $id\n"; // <-- MUDANÇA: Log para depuração
            // Atualiza o mapa para otimizar futuras buscas
            $this->sessionMap[$shortId] = trim($id);
            return $id;
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
            $ip = $this->GetRealUserIp($conn);

            // Verifica se há um ID de operador ou nome de espectador associado à conexão
            $resourceId = $conn->resourceId;
            if (isset($this->connectionData[$resourceId])) {
                $userId = $this->connectionData[$resourceId]['userId'] ?? null; // ID do operador, se existir
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

    private function GetRealUserIp($conn) {

        $ip ="";

        return $ip . " -> ". $conn->remoteAddress;
    }
}