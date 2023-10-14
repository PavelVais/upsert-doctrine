<?php

namespace Pavelvais\UpsertDoctrine\Tests\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Pavelvais\UpsertDoctrine\Provider\ProviderManager;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\Exception\NotSupported;

class ProviderManagerTest extends TestCase
{
    private ProviderManager $providerManager;

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

    public function testGetProviderForSupportedPlatforms(): void
    {
        $supportedPlatforms = [
            'Doctrine\DBAL\Platforms\MySQLPlatform' => 'Pavelvais\UpsertDoctrine\Provider\MariaDbUpsertProvider',
            'Doctrine\DBAL\Platforms\MySQL80Platform' => 'Pavelvais\UpsertDoctrine\Provider\MariaDbUpsertProvider',
            'Doctrine\DBAL\Platforms\MariaDBPlatform' => 'Pavelvais\UpsertDoctrine\Provider\MariaDbUpsertProvider',
        ];

        foreach ($supportedPlatforms as $platform => $expectedProvider) {
            $provider = $this->providerManager->getProvider($platform);
            $this->assertInstanceOf($expectedProvider, $provider);
        }
    }

    public function testGetProviderForUnsupportedPlatform(): void
    {
        $this->expectException(NotSupported::class);
        $this->providerManager->getProvider('Doctrine\DBAL\Platforms\UnsupportedPlatform');
    }
}
