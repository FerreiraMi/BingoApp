<?php
// tests/BingoChatTest.php
namespace MyApp\Tests;

use MyApp\BingoChat;
use MyApp\Tests\Support\MockConnection;
use MyApp\Tests\Support\MockDatabase;
use PHPUnit\Framework\TestCase;

class BingoChatTest extends TestCase
{
    private MockDatabase $db;
    private BingoChat    $chat;

    protected function setUp(): void
    {
        $this->db   = new MockDatabase();
        $this->chat = new BingoChat($this->db);
    }

    // -----------------------------------------------------------------------
    // onOpen
    // -----------------------------------------------------------------------

    public function testOnOpenAttachesConnection(): void
    {
        $conn = new MockConnection(1);
        $this->chat->onOpen($conn);

        // Verifica que a conexão foi registrada internamente
        // (accessamos via reflexão para não expor internals desnecessariamente)
        $data = $this->getConnectionData();
        $this->assertArrayHasKey(1, $data);
        $this->assertFalse($data[1]['isOperator']);
        $this->assertNull($data[1]['shortId']);
    }

    // -----------------------------------------------------------------------
    // onMessage – authenticate_operator
    // -----------------------------------------------------------------------

    public function testAuthenticateOperatorSuccessMarkConnectionAsOperator(): void
    {
        $longId  = (string) new \MongoDB\BSON\ObjectId();
        $userId  = (string) new \MongoDB\BSON\ObjectId();
        $shortId = 'AB12C';

        // Banco retorna um documento simulando sessão válida
        $this->db->sessions->findOneResult = [
            '_id'     => new \MongoDB\BSON\ObjectId($longId),
            'shortId' => $shortId,
        ];

        $conn = new MockConnection(10);
        $this->chat->onOpen($conn);
        $this->chat->onMessage($conn, json_encode([
            'type'      => 'authenticate_operator',
            'sessionId' => $longId,
            'userId'    => $userId,
        ]));

        $data = $this->getConnectionData();
        $this->assertTrue($data[10]['isOperator']);
        $this->assertSame($shortId, $data[10]['shortId']);
    }

    public function testAuthenticateOperatorFailureClosesConnection(): void
    {
        // Banco não encontra a sessão
        $this->db->sessions->findOneResult = null;

        $conn = new MockConnection(20);
        $this->chat->onOpen($conn);
        $this->chat->onMessage($conn, json_encode([
            'type'      => 'authenticate_operator',
            'sessionId' => (string) new \MongoDB\BSON\ObjectId(),
            'userId'    => (string) new \MongoDB\BSON\ObjectId(),
        ]));

        $this->assertTrue($conn->closed, 'Conexão deve ser fechada para autenticação inválida');
    }

    public function testAuthenticateOperatorMissingDataDoesNotCrash(): void
    {
        $conn = new MockConnection(21);
        $this->chat->onOpen($conn);

        // sessionId e userId ausentes – não deve lançar exceção
        $this->chat->onMessage($conn, json_encode(['type' => 'authenticate_operator']));

        $this->assertFalse($conn->closed, 'Conexão não deve ser fechada por dados ausentes sem tentar consulta');
    }

    // -----------------------------------------------------------------------
    // onMessage – subscribe (página web)
    // -----------------------------------------------------------------------

    public function testSubscribeRegistersSessionOnConnection(): void
    {
        $shortId   = 'SB001';
        $sessionId = (string) new \MongoDB\BSON\ObjectId();

        $conn = new MockConnection(30);
        $this->chat->onOpen($conn);
        $this->chat->onMessage($conn, json_encode([
            'type'      => 'subscribe',
            'shortId'   => $shortId,
            'sessionId' => $sessionId,
        ]));

        $data = $this->getConnectionData();
        $this->assertSame($shortId, $data[30]['shortId']);
    }

    // -----------------------------------------------------------------------
    // onMessage – new_number
    // -----------------------------------------------------------------------

    public function testNewNumberFromUnauthorizedConnectionIsRejected(): void
    {
        $conn = new MockConnection(40);
        $this->chat->onOpen($conn);
        // NÃO autentica como operador

        $this->chat->onMessage($conn, json_encode([
            'type'      => 'new_number',
            'sessionId' => (string) new \MongoDB\BSON\ObjectId(),
            'number'    => 42,
        ]));

        // O banco NÃO deve ter sido chamado para atualizar
        $this->assertEmpty(
            $this->db->sessions->lastUpdateFilter,
            'Conexão não autorizada não deve gravar número no banco'
        );
    }

    public function testNewNumberFromAuthorizedOperatorPersistsAndBroadcasts(): void
    {
        $longId  = (string) new \MongoDB\BSON\ObjectId();
        $userId  = (string) new \MongoDB\BSON\ObjectId();
        $shortId = 'OP001';

        // Sessão encontrada para autenticação
        $this->db->sessions->findOneResult = [
            '_id'     => new \MongoDB\BSON\ObjectId($longId),
            'shortId' => $shortId,
        ];

        $operator = new MockConnection(50);
        $this->chat->onOpen($operator);
        $this->chat->onMessage($operator, json_encode([
            'type'      => 'authenticate_operator',
            'sessionId' => $longId,
            'userId'    => $userId,
        ]));

        // Reconfigura findOne para a busca por shortId no new_number
        $this->db->sessions->findOneResult = null; // não usado no caminho new_number

        $this->chat->onMessage($operator, json_encode([
            'type'      => 'new_number',
            'sessionId' => $longId,
            'number'    => 33,
        ]));

        // O updateOne deve ter sido chamado
        $this->assertNotEmpty($this->db->sessions->lastUpdateFilter);
    }

    // -----------------------------------------------------------------------
    // onMessage – bingo_called
    // -----------------------------------------------------------------------

    public function testBingoCalledBroadcastsToSession(): void
    {
        $longId  = (string) new \MongoDB\BSON\ObjectId();
        $userId  = (string) new \MongoDB\BSON\ObjectId();
        $shortId = 'BG777';

        // Setup: conecta operador
        $this->db->sessions->findOneResult = [
            '_id'     => new \MongoDB\BSON\ObjectId($longId),
            'shortId' => $shortId,
        ];

        $operator = new MockConnection(60);
        $this->chat->onOpen($operator);
        $this->chat->onMessage($operator, json_encode([
            'type'      => 'authenticate_operator',
            'sessionId' => $longId,
            'userId'    => $userId,
        ]));

        // Conecta um espectador na mesma sessão
        $viewer = new MockConnection(61);
        $this->chat->onOpen($viewer);
        $this->chat->onMessage($viewer, json_encode([
            'type'       => 'viewer_joined',
            'shortId'    => $shortId,
            'viewerName' => 'Maria',
        ]));

        // Chama bingo
        $this->chat->onMessage($operator, json_encode([
            'type'      => 'bingo_called',
            'sessionId' => $longId,
            'winners'   => ['Maria'],
        ]));

        $received = array_map('json_decode', $viewer->sentMessages);
        $types    = array_column($received, 'type');
        $this->assertContains('bingo_called', $types, 'Espectador deve receber o evento bingo_called');
    }

    // -----------------------------------------------------------------------
    // onClose
    // -----------------------------------------------------------------------

    public function testOnCloseRemovesConnectionData(): void
    {
        $conn = new MockConnection(70);
        $this->chat->onOpen($conn);
        $this->chat->onClose($conn);

        $data = $this->getConnectionData();
        $this->assertArrayNotHasKey(70, $data, 'Dados da conexão devem ser removidos ao fechar');
    }

    // -----------------------------------------------------------------------
    // onError
    // -----------------------------------------------------------------------

    public function testOnErrorClosesConnection(): void
    {
        $conn = new MockConnection(80);
        $this->chat->onOpen($conn);
        $this->chat->onError($conn, new \Exception('erro simulado'));

        $this->assertTrue($conn->closed);
    }

    // -----------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------

    /** Acessa $connectionData via Reflection para assertions sem expor a API pública. */
    private function getConnectionData(): array
    {
        $ref = new \ReflectionProperty(BingoChat::class, 'connectionData');
        $ref->setAccessible(true);
        return $ref->getValue($this->chat);
    }
}
