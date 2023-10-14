<?php declare(strict_types=1);

namespace Pavelvais\UpsertDoctrine\Provider;

use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\NotSupported;

class ProviderManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    /**
     * @var array Mapping of database platforms to their Upsert providers.
     */
    private array $providerMap = [
        MySQLPlatform::class => MariaDbUpsertProvider::class,
        MySQL80Platform::class => MariaDbUpsertProvider::class,
        MariaDBPlatform::class => MariaDbUpsertProvider::class,
    ];

    /**
     * Get the UpsertProvider for a given database platform.
     *
     * @param string $dbPlatform The name of the database platform class.
     * @return ProviderInterface The UpsertProvider instance.
     * @throws NotSupported If the database platform is not supported.
     */
    public function getProvider(string $dbPlatform): ProviderInterface
    {
        if (!isset($this->providerMap[$dbPlatform])) {
            throw new NotSupported("Upsert is not supported on platform {$dbPlatform}.");
        }
        return new $this->providerMap[$dbPlatform]($this->entityManager);
    }
}
