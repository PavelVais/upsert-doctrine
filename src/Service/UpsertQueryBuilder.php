<?php declare(strict_types=1);

namespace Pavelvais\UpsertDoctrine\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\NotSupported;

class UpsertQueryBuilder
{

    private array $allowedPlatforms = [
        MySQLPlatform::class,
        MySQL80Platform::class,
        MariaDBPlatform::class,
    ];

    private Connection $connection;

    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
        $this->connection = $entityManager->getConnection();
    }

    public function upsertQuery(array $data, string $repositoryClass): string
    {
        $this->checkPlatform();
        $table = $this->getTableName($repositoryClass);
        return $this->buildSqlQuery($data, $table);
    }

    public function checkPlatform(): void
    {
        $dbPlatform = get_class($this->connection->getDatabasePlatform());
        $dbParentPlatform = get_parent_class($this->connection->getDatabasePlatform());
        if (!in_array($dbPlatform, $this->allowedPlatforms)
            && !in_array($dbParentPlatform, $this->allowedPlatforms)) {
            throw new NotSupported("Upsert is not supported on platform {$dbPlatform}.");
        }
    }

    private function getTableName(string $repositoryClass): string
    {
        return $this->entityManager->getClassMetadata($repositoryClass)->getTableName();
    }

    private function buildSqlQuery(array $data, string $table): string
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($column) => ':' . $column, $columns);

        $insertColumns = implode(', ', $columns);
        $insertValues = implode(', ', $placeholders);
        $update = implode(', ', array_map(fn($column) => $column . ' = VALUES(' . $column . ')', $columns));

        return sprintf(
            'INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
            $table,
            $insertColumns,
            $insertValues,
            $update
        );
    }

    private function executeQuery(string $sql, array $data): Result
    {
        $statement = $this->connection->prepare($sql);
        foreach ($data as $column => $value) {
            $statement->bindValue(':' . $column, $value);
        }

        return $statement->executeQuery();
    }
}
