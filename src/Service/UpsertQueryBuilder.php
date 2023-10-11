<?php declare(strict_types=1);

namespace Pavelvais\UpsertDoctrine\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\NotSupported;
use Pavelvais\UpsertDoctrine\Provider\ProviderInterface;
use Pavelvais\UpsertDoctrine\Provider\ProviderManager;

class UpsertQueryBuilder
{

    private ?ProviderInterface $upsertProvider = null;

    private Connection $connection;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
        $this->connection = $entityManager->getConnection();
        $this->providerManager = new ProviderManager();
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
     * Byly pridany provideri pro jednotlive platformy, díky čemuž se projekt stal více rozšiřitelným.
     * @throws NotSupported
     * @throws Exception
     */
    public function checkPlatform(): void
    {
        $dbPlatform = get_class($this->connection->getDatabasePlatform());
        $dbParentPlatform = get_parent_class($this->connection->getDatabasePlatform());

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
