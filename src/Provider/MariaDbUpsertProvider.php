<?php declare(strict_types=1);

namespace Pavelvais\UpsertDoctrine\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Pavelvais\UpsertDoctrine\Exception\InvalidUpsertArguments;

class MariaDbUpsertProvider implements ProviderInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

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

    /**
     * Get SQL query string for UPSERT (INSERT ... ON DUPLICATE KEY UPDATE) operations.
     *
     * @param array $data The data rows to be upserted.
     *                    Each row should be an associative array where the key is the column name.
     * @param string $table The name of the table into which the data will be upserted.
     *
     * @return string|null The SQL query string for the UPSERT operation,
     *                     or null if the input data array is empty.
     *
     * @throws InvalidUpsertArguments If there are invalid attributes in the data array.
     *
     * @example
     *   $this->upsertBatchQuery([
     *       [
     *           'id' => 1,
     *           'column1' => 'value1',
     *           'column2' => 'value2',
     *       ],
     *       [
     *           'id' => 2,
     *           'column1' => 'value3',
     *           'column2' => 'value4',
     *       ],
     *   ], entity::class);
     *
     *  This would try to insert two rows in 'entity table name'. If rows with the same unique id already exist,
     *  it would update 'column1' and 'column2' for these rows.
     */
    public function getUpsertBatchQuery(array $data, string $table): ?string
    {
        if (empty($data)) {
            return null;
        }

        $columns = array_keys($data[0]);
        $placeholders = array_map(fn($column) => ':' . $column, $columns);
        $insertColumns = implode(', ', $columns);
        $insertValues = [];

        $updateClausule = implode(
            ', ', array_map(fn($column) => $column . ' = VALUES(' . $column . ')', $columns)
        );

        foreach ($data as $attributes) {
            $inClausule = implode(
                separator: ', ',
                array: array_map(
                    fn($placeholder) => $this->escapeAttribute($attributes[substr($placeholder, 1)]),
                    $placeholders
                )
            );
            $insertValues[] = "($inClausule)";
        }

        // Sestavíme a provedeme dotaz pro všechny řádky
        return sprintf(
            'INSERT INTO %s (%s) VALUES %s ON DUPLICATE KEY UPDATE %s',
            $table,
            $insertColumns,
            implode(', ', $insertValues),
            $updateClausule
        );
    }

    /**
     * Escape an attribute for SQL queries.
     *
     * This method prepares an attribute for use in a SQL query by escaping it
     * based on its data type. The escaped value is either a string, an integer, or
     * a string representation of the NULL keyword.
     *
     * @param mixed $attribute The attribute to be escaped.
     *
     * @return string|int The escaped attribute value, ready for use in a SQL query.
     *
     * @throws InvalidUpsertArguments If the attribute's data type is not supported for escaping.
     */
    private function escapeAttribute(mixed $attribute): string|int
    {
        return match (strtolower(gettype($attribute))) {
            'integer', 'double' => $attribute,
            'string' => "'" . $this->entityManager->getConnection()->quote($attribute) . "'",
            'array', 'object', 'resource' => throw InvalidUpsertArguments::invalidAttribute(gettype($attribute)),
            'null' => 'NULL',
            'boolean' => $attribute ? 1 : 0,
            default => throw InvalidUpsertArguments::notSupportedAttribute()
        };
    }
}
