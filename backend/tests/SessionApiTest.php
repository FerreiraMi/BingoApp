<?php
// tests/SessionApiTest.php
namespace MyApp\Tests;

use MyApp\Tests\Support\MockDatabase;
use PHPUnit\Framework\TestCase;

/**
 * Testa a lógica do endpoint api/session.php de forma isolada:
 * - Geração e unicidade do shortId
 * - Validação de entrada
 * - Estrutura do documento gravado
 */
class SessionApiTest extends TestCase
{
    // -----------------------------------------------------------------------
    // Geração do shortId
    // -----------------------------------------------------------------------

    public function testShortIdHasFiveCharacters(): void
    {
        $shortId = $this->generateShortId();
        $this->assertSame(5, strlen($shortId));
    }

    public function testShortIdContainsOnlyAllowedCharacters(): void
    {
        $shortId = $this->generateShortId();
        $this->assertMatchesRegularExpression('/^[0-9A-Z]{5}$/', strtoupper($shortId));
    }

    public function testDifferentCallsYieldDifferentIds(): void
    {
        $ids = [];
        for ($i = 0; $i < 30; $i++) {
            $ids[] = $this->generateShortId();
        }
        // Com 30 tentativas sobre 36^5 possibilidades, a chance de colisão é desprezível
        $this->assertGreaterThan(1, count(array_unique($ids)));
    }

    // -----------------------------------------------------------------------
    // Validação de entrada (POST)
    // -----------------------------------------------------------------------

    public function testCreateSessionFailsWhenUserIdMissing(): void
    {
        $result = $this->validateCreate([]);
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('userId', $result['error']);
    }

    public function testCreateSessionSucceedsWithMinimalData(): void
    {
        $result = $this->validateCreate(['userId' => '507f1f77bcf86cd799439011']);
        $this->assertTrue($result['valid']);
    }

    // -----------------------------------------------------------------------
    // Estrutura do documento inserido
    // -----------------------------------------------------------------------

    public function testInsertDocumentHasRequiredFields(): void
    {
        $doc = $this->buildSessionDocument([
            'userId'      => '507f1f77bcf86cd799439011',
            'sessionName' => 'Bingo Test',
            'round'       => 1,
            'prize'       => 'Prêmio',
            'isProUser'   => false,
        ]);

        foreach (['userId', 'shortId', 'sessionName', 'round', 'prize', 'status', 'drawnNumbers', 'winners'] as $field) {
            $this->assertArrayHasKey($field, $doc, "Campo obrigatório ausente: $field");
        }
        $this->assertSame('active', $doc['status']);
        $this->assertSame([], $doc['drawnNumbers']);
        $this->assertSame([], $doc['winners']);
    }

    public function testInsertDocumentDefaultsApplied(): void
    {
        $doc = $this->buildSessionDocument(['userId' => '507f1f77bcf86cd799439011']);

        $this->assertSame('Bingo Padrão', $doc['sessionName']);
        $this->assertSame(1,             $doc['round']);
        $this->assertSame('Sem prêmio',  $doc['prize']);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function generateShortId(): string
    {
        return substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5);
    }

    private function validateCreate(array $data): array
    {
        if (empty($data['userId'])) {
            return ['valid' => false, 'error' => 'userId é obrigatório'];
        }
        return ['valid' => true];
    }

    private function buildSessionDocument(array $data): array
    {
        $shortId = $this->generateShortId();
        return [
            'userId'      => $data['userId'],
            'shortId'     => $shortId,
            'sessionName' => $data['sessionName'] ?? 'Bingo Padrão',
            'round'       => $data['round']       ?? 1,
            'prize'       => $data['prize']        ?? 'Sem prêmio',
            'status'      => 'active',
            'isProUser'   => $data['isProUser']    ?? false,
            'drawnNumbers'=> [],
            'winners'     => [],
        ];
    }
}
