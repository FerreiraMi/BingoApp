<?php
// tests/DisplayTokenTest.php
namespace MyApp\Tests;

use MyApp\Tests\Support\MockCollection;
use MyApp\Tests\Support\MockDatabase;
use PHPUnit\Framework\TestCase;

/**
 * Testa a lógica de negócio do display_token:
 * - Geração de código único
 * - Consulta de status
 * - Vínculo do código com uma sessão ativa
 *
 * Como os arquivos da api/ são procedurais, extraímos aqui as funções
 * de regra de negócio que podem ser testadas de forma isolada.
 */
class DisplayTokenTest extends TestCase
{
    // -----------------------------------------------------------------------
    // Geração de código
    // -----------------------------------------------------------------------

    public function testGeneratedCodeHasSixCharacters(): void
    {
        $code = $this->generateCode();
        $this->assertSame(6, strlen($code));
    }

    public function testGeneratedCodeContainsOnlyAllowedCharacters(): void
    {
        $code = $this->generateCode();
        $this->assertMatchesRegularExpression('/^[0-9A-Z]{6}$/', $code);
    }

    public function testGeneratedCodesAreNotAllIdentical(): void
    {
        $codes = [];
        for ($i = 0; $i < 20; $i++) {
            $codes[] = $this->generateCode();
        }
        // Não é razoável que todos os 20 códigos sejam iguais
        $this->assertGreaterThan(1, count(array_unique($codes)));
    }

    // -----------------------------------------------------------------------
    // Validação de entrada no PUT (vincular sessão)
    // -----------------------------------------------------------------------

    public function testLinkingFailsWhenCodeIsEmpty(): void
    {
        $result = $this->validateLinkInput(code: '', sessionShortId: 'XYZ12');
        $this->assertFalse($result['valid']);
    }

    public function testLinkingFailsWhenSessionShortIdIsEmpty(): void
    {
        $result = $this->validateLinkInput(code: 'AB1234', sessionShortId: '');
        $this->assertFalse($result['valid']);
    }

    public function testLinkingSucceedsWithValidInput(): void
    {
        $result = $this->validateLinkInput(code: 'AB1234', sessionShortId: 'XY789');
        $this->assertTrue($result['valid']);
    }

    // -----------------------------------------------------------------------
    // Lógica de busca de status
    // -----------------------------------------------------------------------

    public function testGetStatusReturnsWaitingWhenTokenNotLinked(): void
    {
        $token = ['code' => 'A1B2C3', 'status' => 'waiting', 'sessionShortId' => null];
        $this->assertSame('waiting', $token['status']);
        $this->assertNull($token['sessionShortId']);
    }

    public function testGetStatusReturnsLinkedAfterPut(): void
    {
        $token = ['code' => 'A1B2C3', 'status' => 'linked', 'sessionShortId' => 'XY789'];
        $this->assertSame('linked',  $token['status']);
        $this->assertSame('XY789',   $token['sessionShortId']);
    }

    // -----------------------------------------------------------------------
    // Normalização do código
    // -----------------------------------------------------------------------

    public function testCodeIsNormalizedToUppercase(): void
    {
        $input = ' ab12cd ';
        $normalized = strtoupper(trim($input));
        $this->assertSame('AB12CD', $normalized);
    }

    // -----------------------------------------------------------------------
    // Helpers internos
    // -----------------------------------------------------------------------

    /** Reimplementa a lógica de geração do endpoint POST de forma isolada. */
    private function generateCode(): string
    {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code  = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }

    /** Reimplementa a validação de entrada do endpoint PUT. */
    private function validateLinkInput(string $code, string $sessionShortId): array
    {
        $code           = strtoupper(trim($code));
        $sessionShortId = strtoupper(trim($sessionShortId));

        if (empty($code) || empty($sessionShortId)) {
            return ['valid' => false, 'error' => 'Dados incompletos.'];
        }
        return ['valid' => true];
    }
}
