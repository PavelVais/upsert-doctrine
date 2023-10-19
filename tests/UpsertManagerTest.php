<?php

namespace Pavelvais\UpsertDoctrine\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Pavelvais\UpsertDoctrine\Builder\UpsertQueryBuilder;
use Pavelvais\UpsertDoctrine\UpsertManager;
use PHPUnit\Framework\TestCase;

class UpsertManagerTest extends TestCase
{
    private UpsertManager $upsertManager;
    private Connection $connection;

    protected function setUp(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $this->connection = $this->createMock(Connection::class);
        $entityManager->method('getConnection')->willReturn($this->connection);
        $this->upsertManager = new UpsertManager($entityManager);
    }

    public function testExecute()
    {
        $data = ['field' => 'value'];
        $repositoryClass = 'MyRepositoryClass';
        $query = 'SQL_QUERY';
        $params = [':field' => 'value'];

        $upsertQueryBuilder = $this->createMock(UpsertQueryBuilder::class);
        $upsertQueryBuilder->method('upsertQuery')->willReturn($query);

        $this->connection->expects($this->once())->method('executeStatement')->with($query, $params)->willReturn(1);

        $result = $this->upsertManager->execute($data, $repositoryClass);

        $this->assertEquals(1, $result);
    }

    public function testExecuteBatch()
    {
        $data = ['field' => 'value'];
        $repositoryClass = 'MyRepositoryClass';
        $query = 'SQL_BATCH_QUERY';

        $upsertQueryBuilder = $this->createMock(UpsertQueryBuilder::class);
        $upsertQueryBuilder->method('upsertBatchQuery')->willReturn($query);

        $this->connection->expects($this->once())->method('executeStatement')->with($query)->willReturn(1);

        $result = $this->upsertManager->executeBatch($data, $repositoryClass);

        $this->assertEquals(1, $result);
    }

    public function testExecuteThrowsException()
    {
        $this->expectException(Exception::class);

        $data = ['field' => 'value'];
        $repositoryClass = 'MyRepositoryClass';

        $this->connection->expects($this->once())->method('executeStatement')->willThrowException(new Exception());

        $this->upsertManager->execute($data, $repositoryClass);
    }
}
