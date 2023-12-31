<?php

namespace Pavelvais\UpsertDoctrine\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\NotSupported;
use Pavelvais\UpsertDoctrine\ProviderManager;
use PHPUnit\Framework\TestCase;

class ProviderManagerTest extends TestCase
{
    private ProviderManager $providerManager;

    public function supportedPlatformsProvider(): array
    {
        return [
            ['Doctrine\DBAL\Platforms\MySQLPlatform', 'Pavelvais\UpsertDoctrine\Provider\MariaDbUpsertProvider'],
            ['Doctrine\DBAL\Platforms\MySQL80Platform', 'Pavelvais\UpsertDoctrine\Provider\MariaDbUpsertProvider'],
            ['Doctrine\DBAL\Platforms\MariaDBPlatform', 'Pavelvais\UpsertDoctrine\Provider\MariaDbUpsertProvider'],
        ];
    }

    protected function setUp(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn(
            new class {
                // simplified quote method
                public function quote($input): string
                {
                    return addslashes($input); // simplified mock
                }
            }
        );
        $this->providerManager = new ProviderManager($entityManager);
    }

    /**
     * @dataProvider supportedPlatformsProvider
     */
    public function testGetProviderForSupportedPlatforms($platform, $expectedProvider): void
    {
        $provider = $this->providerManager->getProvider($platform);
        $this->assertInstanceOf($expectedProvider, $provider);
    }

    public function testGetProviderForUnsupportedPlatform(): void
    {
        $this->expectException(NotSupported::class);
        $this->providerManager->getProvider('Doctrine\DBAL\Platforms\UnsupportedPlatform');
    }
}
