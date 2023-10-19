<?php declare(strict_types=1);

namespace Pavelvais\UpsertDoctrine;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\NotSupported;
use Pavelvais\UpsertDoctrine\Builder\UpsertQueryBuilder;

class UpsertManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
    }

    /**
     * @throws NotSupported
     * @throws Exception
     */
    public function execute(array $data, string $repositoryClass): int
    {
        $query = (new UpsertQueryBuilder($this->entityManager))->upsertQuery($data, $repositoryClass);
        $params = $this->prepareParams($data);

        return $this->executeQuery($query, $params);
    }

    /**
     * Executes a batch operation on a database using the provided data and repository class.
     *
     * @param array $data The data to be used in the batch operation.
     * @param string $repositoryClass The class name of the repository to be used for the batch operation.
     * @return int The number of affected rows in the database.
     * @throws NotSupported if the batch operation is not supported.
     * @throws Exception if an error occurs during the batch operation.
     */
    public function executeBatch(array $data, string $repositoryClass): int
    {
        $query = (new UpsertQueryBuilder($this->entityManager))->upsertBatchQuery($data, $repositoryClass);

        return $this->executeQuery($query);
    }

    /**
     * Execute query using Entity manager connection
     *
     * @param string $query
     * @param array $params
     * @return int
     * @throws Exception
     */
    private function executeQuery(string $query, array $params = []): int
    {
        return $this->entityManager
            ->getConnection()
            ->executeStatement($query, $params);
    }

    /**
     * Prepare parameters for query
     *
     * @param array $data
     * @return array
     */
    private function prepareParams(array $data): array
    {
        $params = array_map(fn($key) => ':' . $key, array_keys($data));
        return array_combine($params, array_values($data));
    }
}
