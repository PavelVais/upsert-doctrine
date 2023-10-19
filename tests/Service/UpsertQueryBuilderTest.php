<?php

declare(strict_types=1);

namespace Pavelvais\UpsertDoctrine\Tests\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\NotSupported;
use Pavelvais\UpsertDoctrine\Builder\UpsertQueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpsertQueryBuilderTest extends TestCase
{
    private EntityManagerInterface|MockObject $entityManager;

    public function setUp(): void
    {
        $platform = $this->createMock(MySQL80Platform::class);
        $connection = $this->createMock(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn($platform);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entityManager->method('getConnection')->willReturn($connection);
    }

    public function testConstructorInitializesConnection(): void
    {
        $upsertQueryBuilder = new UpsertQueryBuilder($this->entityManager);
        $this->assertInstanceOf(UpsertQueryBuilder::class, $upsertQueryBuilder);
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
}
