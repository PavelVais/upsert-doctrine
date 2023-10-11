<?php declare(strict_types=1);

namespace Pavelvais\UpsertDoctrine\Provider;

class MariaDbUpsertProvider implements ProviderInterface
{
    /**
     * Generates an upsert query for inserting or updating data in a table.
     *
     * @param array $data An associative array of data to be inserted or updated.
     * @param string $tableName The name of the table where the data will be inserted or updated.
     * @return string The upsert query.
     */
    public function getUpsertQuery(array $data, string $tableName): string
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($column) => ':' . $column, $columns);

        $insertColumns = implode(', ', $columns);
        $insertValues = implode(', ', $placeholders);
        $update = implode(', ', array_map(fn($column) => $column . ' = VALUES(' . $column . ')', $columns));

        return sprintf(
            'INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
            $tableName,
            $insertColumns,
            $insertValues,
            $update
        );
    }
}
