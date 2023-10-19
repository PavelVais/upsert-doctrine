<?php declare(strict_types=1);

namespace Pavelvais\UpsertDoctrine\Builder;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\NotSupported;
use Pavelvais\UpsertDoctrine\Provider\ProviderInterface;
use Pavelvais\UpsertDoctrine\ProviderManager;

class UpsertQueryBuilder
{

    private ?ProviderInterface $upsertProvider = null;

    private Connection $connection;
    private ProviderManager $providerManager;

    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
        $this->connection = $entityManager->getConnection();
        $this->providerManager = new ProviderManager($entityManager);
    }

    /**
     * Builds and returns an upsert query.
     *
     * @param array $data Data to insert or update.
     * @param string $repositoryClass Repository class to find the table name.
     *
     * @return string The SQL query string for upsert.
     *
     * @throws NotSupported If the database platform is not supported.
     */
    public function upsertQuery(array $data, string $repositoryClass): string
    {
        $this->checkPlatform();
        $table = $this->getTableName($repositoryClass);
        return $this->upsertProvider->getUpsertQuery($data, $table);
    }

    /**
     * @throws NotSupported
     */
    public function upsertBatchQuery(array $data, string $repositoryClass): string
    {
        $this->checkPlatform();
        $table = $this->getTableName($repositoryClass);
        return $this->upsertProvider->getUpsertBatchQuery($data, $table);
    }

    /**
     * @throws NotSupported
     */
    public function checkPlatform(): void
    {
        try {
            $dbPlatform = get_class($this->connection->getDatabasePlatform());
            $dbParentPlatform = get_parent_class($this->connection->getDatabasePlatform());
        } catch (Exception $e) {
            throw new NotSupported("Platform is misconfigured.", previous: $e);
        }

        try {
            $this->upsertProvider = $this->providerManager->getProvider($dbPlatform);
        } catch (NotSupported $e) {
            if ($dbParentPlatform) {
                $this->upsertProvider = $this->providerManager->getProvider($dbParentPlatform);
            } else {
                throw $e;
            }
        }
    }

    /**
     * Gets the table name from the repository class.
     *
     * @param string $repositoryClass Repository class to find the table name.
     *
     * @return string The table name.
     */
    private function getTableName(string $repositoryClass): string
    {
        return $this->entityManager->getClassMetadata($repositoryClass)->getTableName();
    }
}
