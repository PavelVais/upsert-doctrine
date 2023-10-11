<?php declare(strict_types=1);

namespace Pavelvais\UpsertDoctrine\Provider;

Interface ProviderInterface
{
    public function getUpsertQuery(array $data, string $tableName): string;

}
