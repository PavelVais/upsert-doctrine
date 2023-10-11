<?php declare(strict_types=1);

namespace Pavelvais\UpsertDoctrine\Tests\Provider;

use Pavelvais\UpsertDoctrine\Provider\MariaDbUpsertProvider;
use PHPUnit\Framework\TestCase;

class MariaDbUpsertProviderTest extends TestCase
{
    private MariaDbUpsertProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new MariaDbUpsertProvider();
    }

    /**
     * @dataProvider upsertQueryDataProvider
     */
    public function testGetUpsertQuery(array $data, string $tableName, string $expectedQuery): void
    {
        $actualQuery = $this->provider->getUpsertQuery($data, $tableName);
        $this->assertSame($expectedQuery, $actualQuery);
    }

    public function upsertQueryDataProvider(): array
    {
        return [
            'case 1' => [
                'data' => [
                    'column1' => 'value1',
                    'column2' => 'value2',
                    'column3' => 'value3'
                ],
                'tableName' => 'test_table',
                'expectedQuery' => 'INSERT INTO test_table (column1, column2, column3) '
                    . 'VALUES (:column1, :column2, :column3) '
                    . 'ON DUPLICATE KEY UPDATE column1 = VALUES(column1), column2 = VALUES(column2), column3 = VALUES(column3)'
            ],
            'case 2' => [
                'data' => [
                    'columnA' => 'valueA',
                    'columnB' => 'valueB'
                ],
                'tableName' => 'another_table',
                'expectedQuery' => 'INSERT INTO another_table (columnA, columnB) '
                    . 'VALUES (:columnA, :columnB) '
                    . 'ON DUPLICATE KEY UPDATE columnA = VALUES(columnA), columnB = VALUES(columnB)'
            ],
            // ... additional cases
        ];
    }
}
