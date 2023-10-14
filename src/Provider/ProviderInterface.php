<?php declare(strict_types=1);

namespace Pavelvais\UpsertDoctrine\Provider;

use Doctrine\ORM\EntityManagerInterface;

interface ProviderInterface
{
    public function __construct(
        EntityManagerInterface $entityManager,
    );

    public function getUpsertQuery(array $data, string $tableName): string;

    public function getUpsertBatchQuery(array $data, string $table);

}
