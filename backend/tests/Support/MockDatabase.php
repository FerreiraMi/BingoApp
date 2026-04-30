<?php
// tests/Support/MockDatabase.php
namespace MyApp\Tests\Support;

/**
 * Banco de dados falso para injetar no BingoChat durante os testes.
 * Expõe as collections como propriedades para imitar o comportamento
 * do \MongoDB\Database (que usa __get para retornar coleções).
 */
class MockDatabase
{
    public MockCollection $sessions;
    public MockCollection $display_tokens;

    public function __construct()
    {
        $this->sessions       = new MockCollection();
        $this->display_tokens = new MockCollection();
    }

    /** Compatibilidade com código que acesse $db->nome_collection */
    public function __get(string $name): MockCollection
    {
        return $this->$name;
    }
}

/**
 * Coleção MongoDB falsa.
 * Permite configurar o resultado de findOne, updateOne, countDocuments
 * antes da chamada, e guardar os filtros recebidos para assertions.
 */
class MockCollection
{
    public mixed $findOneResult = null;
    public int   $modifiedCount = 1;
    public int   $countDocumentsResult = 0;

    /** Último filtro recebido em findOne */
    public array $lastFindFilter = [];
    /** Último filtro e update recebidos em updateOne */
    public array $lastUpdateFilter = [];
    public array $lastUpdateData   = [];
    /** Todos os documentos inseridos */
    public array $insertedDocuments = [];

    public function findOne(array $filter, array $options = []): mixed
    {
        $this->lastFindFilter = $filter;
        return $this->findOneResult;
    }

    public function updateOne(array $filter, array $update, array $options = []): MockUpdateResult
    {
        $this->lastUpdateFilter = $filter;
        $this->lastUpdateData   = $update;
        return new MockUpdateResult($this->modifiedCount);
    }

    public function insertOne(array $document): MockInsertResult
    {
        $this->insertedDocuments[] = $document;
        return new MockInsertResult();
    }

    public function countDocuments(array $filter = []): int
    {
        return $this->countDocumentsResult;
    }
}

class MockUpdateResult
{
    public function __construct(private int $count) {}
    public function getModifiedCount(): int { return $this->count; }
}

class MockInsertResult
{
    public function getInsertedId(): \MongoDB\BSON\ObjectId
    {
        return new \MongoDB\BSON\ObjectId();
    }
}
