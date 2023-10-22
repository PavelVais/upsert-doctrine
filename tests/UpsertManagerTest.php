<?php

namespace Pavelvais\UpsertDoctrine\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Pavelvais\UpsertDoctrine\UpsertManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpsertManagerTest extends TestCase
{
    private UpsertManager $upsertManager;
    private Connection|MockObject $connection;

    protected function setUp(): void
    {
        $this->connection = $this->setupMockConnection();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($this->connection);
        $entityManager->method('getClassMetadata')->willReturn(
            new class {
                public function getTableName(): string
                {
                    return 'book_author';
                }
            }
        );

        $this->upsertManager = new UpsertManager($entityManager);
    }

    private function setupMockConnection(): Connection|MockObject
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn($this->createMock(MySQL80Platform::class));
        $connection->method('quote')->willReturnCallback(fn($input) => addslashes($input));

        return $connection;
    }

    public function testExecuteWithValidData(): void
    {
        $data = $this->getTestData();
        $this->connection
            ->expects($this->once())
            ->method('executeStatement')
            ->willReturn(1);

        $result = $this->upsertManager->execute($data, 'FooRepositoryClass');

        $this->assertEquals(1, $result);
    }

    public function testExecuteBatchWithValidData(): void
    {
        $data = $this->getTestBatchData();
        $this->connection->expects($this->once())->method('executeStatement')->willReturn(2);

        $result = $this->upsertManager->executeBatch($data, 'FooRepositoryClass');

        $this->assertEquals(2, $result);
    }

    public function testExecuteWithException(): void
    {
        $this->expectException(Exception::class);

        $this->connection
            ->expects($this->once())
            ->method('executeStatement')
            ->willThrowException(new Exception());

        $this->upsertManager->execute(
            data: ['column' => 'value'],
            repositoryClass: 'FooRepositoryClass'
        );
    }

    private function getTestData(): array
    {
        return [
            'book_id' => 1,
            'author_id' => 2,
        ];
    }

    private function getTestBatchData(): array
    {
        return [
            [
                'book_id' => 1,
                'author_id' => 2,
                'ownership_type' => 'owner',
            ],
            [
                'book_id' => 3,
                'author_id' => 4,
                'ownership_type' => 'co-author',
            ],
        ];
    }
}
