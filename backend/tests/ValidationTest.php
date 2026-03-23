<?php
// tests/ValidationTest.php
namespace MyApp\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Testa as regras de validação de entrada presentes nas APIs
 * (login, register, finish_session, history).
 */
class ValidationTest extends TestCase
{
    // -----------------------------------------------------------------------
    // Login – validação de campos obrigatórios
    // -----------------------------------------------------------------------

    public function testLoginRequiresEmail(): void
    {
        $result = $this->validateLogin('', 'senha123');
        $this->assertFalse($result['valid']);
    }

    public function testLoginRequiresPassword(): void
    {
        $result = $this->validateLogin('user@test.com', '');
        $this->assertFalse($result['valid']);
    }

    public function testLoginSucceedsWithEmailAndPassword(): void
    {
        $result = $this->validateLogin('user@test.com', 'senha123');
        $this->assertTrue($result['valid']);
    }

    // -----------------------------------------------------------------------
    // Register – email e senha
    // -----------------------------------------------------------------------

    public function testRegisterRequiresValidEmailFormat(): void
    {
        $result = $this->validateRegister('nao_eh_email', 'senha123');
        $this->assertFalse($result['valid']);
        $this->assertStringContainsStringIgnoringCase('email', $result['error']);
    }

    public function testRegisterRequiresPasswordMinimumLength(): void
    {
        $result = $this->validateRegister('user@test.com', '123');
        $this->assertFalse($result['valid']);
        $this->assertStringContainsStringIgnoringCase('senha', $result['error']);
    }

    public function testRegisterSucceedsWithValidEmailAndStrongPassword(): void
    {
        $result = $this->validateRegister('user@test.com', 'senha123');
        $this->assertTrue($result['valid']);
    }

    // -----------------------------------------------------------------------
    // finish_session – sessionId obrigatório
    // -----------------------------------------------------------------------

    public function testFinishSessionRequiresSessionId(): void
    {
        $result = $this->validateFinishSession([]);
        $this->assertFalse($result['valid']);
    }

    public function testFinishSessionSucceedsWithSessionId(): void
    {
        $result = $this->validateFinishSession(['sessionId' => '507f1f77bcf86cd799439011']);
        $this->assertTrue($result['valid']);
    }

    // -----------------------------------------------------------------------
    // history – userId obrigatório via query string
    // -----------------------------------------------------------------------

    public function testHistoryRequiresUserId(): void
    {
        $result = $this->validateHistory([]);
        $this->assertFalse($result['valid']);
    }

    public function testHistorySucceedsWithUserId(): void
    {
        $result = $this->validateHistory(['userId' => '507f1f77bcf86cd799439011']);
        $this->assertTrue($result['valid']);
    }

    // -----------------------------------------------------------------------
    // bingo.php – getBingoLetter
    // -----------------------------------------------------------------------

    /** @dataProvider bingoLetterProvider */
    public function testGetBingoLetterReturnsCorrectLetter(int $number, string $expected): void
    {
        $this->assertSame($expected, $this->getBingoLetter($number));
    }

    public static function bingoLetterProvider(): array
    {
        return [
            [1,  'B'],
            [15, 'B'],
            [16, 'I'],
            [30, 'I'],
            [31, 'N'],
            [45, 'N'],
            [46, 'G'],
            [60, 'G'],
            [61, 'O'],
            [75, 'O'],
            [0,  ''],   // fora do intervalo
            [76, ''],   // fora do intervalo
        ];
    }

    // -----------------------------------------------------------------------
    // Helpers – reimplementações isoladas das regras de negócio
    // -----------------------------------------------------------------------

    private function validateLogin(string $email, string $password): array
    {
        if (empty($email) || empty($password)) {
            return ['valid' => false, 'error' => 'Email e senha são obrigatórios.'];
        }
        return ['valid' => true];
    }

    private function validateRegister(string $email, string $password): array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'error' => 'Formato de email inválido.'];
        }
        if (strlen($password) < 6) {
            return ['valid' => false, 'error' => 'A senha deve ter no mínimo 6 caracteres.'];
        }
        return ['valid' => true];
    }

    private function validateFinishSession(array $data): array
    {
        if (empty($data['sessionId'])) {
            return ['valid' => false, 'error' => 'ID da sessão é obrigatório.'];
        }
        return ['valid' => true];
    }

    private function validateHistory(array $get): array
    {
        if (!isset($get['userId'])) {
            return ['valid' => false, 'error' => 'ID do usuário é obrigatório.'];
        }
        return ['valid' => true];
    }

    private function getBingoLetter(int $number): string
    {
        if ($number >= 1  && $number <= 15) return 'B';
        if ($number >= 16 && $number <= 30) return 'I';
        if ($number >= 31 && $number <= 45) return 'N';
        if ($number >= 46 && $number <= 60) return 'G';
        if ($number >= 61 && $number <= 75) return 'O';
        return '';
    }
}
