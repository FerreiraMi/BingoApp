<?php
// tests/Support/MockConnection.php
namespace MyApp\Tests\Support;

use Ratchet\ConnectionInterface;

/**
 * Implementação falsa de ConnectionInterface para os testes do BingoChat.
 * Grava mensagens enviadas e sinaliza se foi fechada.
 */
class MockConnection implements ConnectionInterface
{
    public int    $resourceId;
    public string $remoteAddress = '127.0.0.1:9999';

    /** @var array<string> Mensagens enviadas via send() */
    public array $sentMessages = [];

    public bool $closed = false;

    public function __construct(int $resourceId)
    {
        $this->resourceId = $resourceId;
    }

    public function send($data): void
    {
        $this->sentMessages[] = $data;
    }

    public function close(): void
    {
        $this->closed = true;
    }
}
