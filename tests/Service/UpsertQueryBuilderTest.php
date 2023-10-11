<?php

declare(strict_types=1);

namespace Pavelvais\UpsertDoctrine\Tests\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Mapping\ClassMetadata;
use Pavelvais\UpsertDoctrine\Service\UpsertQueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpsertQueryBuilderTest extends TestCase
{
    private EntityManagerInterface|MockObject $entityManager;
    private Connection|MockObject $connection;

    public function setUp(): void
    {

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->connection = $this->createMock(Connection::class);
        $platform = $this->createMock(MySQL80Platform::class);

        $this->entityManager->method('getConnection')->willReturn($this->connection);
        $this->connection->method('getDatabasePlatform')->willReturn($platform);
    }

    public function testConstructorInitializesConnection(): void
    {
        $upsertQueryBuilder = new UpsertQueryBuilder($this->entityManager);
        $this->assertInstanceOf(UpsertQueryBuilder::class, $upsertQueryBuilder);
    }

    /**
     * @dataProvider upsertQueryDataProvider
     */
    public function testUpsertQueryGeneratesCorrectSQL(): void
    {
        $data = ['column1' => 'value1', 'column2' => 'value2'];
        $repositoryClass = 'SomeClass';
        $table = 'some_table';

        $connection = $this->createPartialMock(Connection::class, ['getDatabasePlatform']);
        $connection->method('getDatabasePlatform')->willReturn(new MySQL80Platform);

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->method('getTableName')->willReturn($table);

        $this->entityManager
            ->method('getClassMetadata')
            ->with($repositoryClass)
            ->willReturn($classMetadata);

        $this->entityManager->method('getConnection')->willReturn($connection);

        $upsertQueryBuilder = new UpsertQueryBuilder($this->entityManager);
        $sql = $upsertQueryBuilder->upsertQuery($data, $repositoryClass);
        $this->assertSame("INSERT INTO some_table (column1, column2) VALUES (:column1, :column2) ON DUPLICATE KEY UPDATE column1 = VALUES(column1), column2 = VALUES(column2)", $sql);
    }

    public function testCheckPlatformThrowsExceptionForUnsupportedPlatform(): void
    {
        $connection = $this->createPartialMock(Connection::class, ['getDatabasePlatform']);
        $connection->method('getDatabasePlatform')->willReturn(new PostgreSQLPlatform());

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($connection);

        $this->expectException(NotSupported::class);

        $upsertQueryBuilder = new UpsertQueryBuilder($entityManager);
        $upsertQueryBuilder->checkPlatform();
    }

    public function upsertQueryDataProvider(): array
    {
        return [
            [
                ['column1' => null],
                "INSERT INTO some_table (column1, column2) VALUES (:column1, :column2) ON DUPLICATE KEY UPDATE column1 = VALUES(column1)",
            ],
            [
                ['column1' => 'value1', 'column2' => 'value2'],
                "INSERT INTO some_table (column1, column2) VALUES (:column1, :column2) ON DUPLICATE KEY UPDATE column1 = VALUES(column1), column2 = VALUES(column2)",
            ],
            [
                ['column1' => null, 'column2' => 'value2', 'column3' => 'value3'],
                "INSERT INTO some_table (column1, column2) VALUES (:column1, :column2) ON DUPLICATE KEY UPDATE column1 = VALUES(column1), column2 = VALUES(column2), column3 = VALUES(column3)",
            ],
        ];
    }
}
