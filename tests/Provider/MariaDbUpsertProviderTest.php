<?php declare(strict_types=1);

namespace Pavelvais\UpsertDoctrine\Tests\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Pavelvais\UpsertDoctrine\Exception\InvalidUpsertArguments;
use Pavelvais\UpsertDoctrine\Provider\MariaDbUpsertProvider;
use PHPUnit\Framework\TestCase;

class MariaDbUpsertProviderTest extends TestCase
{
    private MariaDbUpsertProvider $provider;

    protected function setUp(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn(
            new class {
                // simplified quote method
                public function quote($input): string
                {
                    return addslashes($input); // simplified mock
                }
            }
        );

        $this->provider = new MariaDbUpsertProvider($entityManager);

    }

    /**
     * @dataProvider upsertQueryDataProvider
     */
    public function testGetUpsertQuery(array $data, string $tableName, string $expectedQuery): void
    {
        $actualQuery = $this->provider->getUpsertQuery($data, $tableName);
        $this->assertSame($expectedQuery, $actualQuery);
    }

    /**
     * @dataProvider upsertBatchQueryDataProvider
     */
    public function testGetUpsertBatchQuery(array $data, string $tableName, string $expectedQuery): void
    {
        $actualQuery = $this->provider->getUpsertBatchQuery($data, $tableName);
        $this->assertSame($expectedQuery, $actualQuery);
    }

    /**
     * @dataProvider upsertBatchQueryDataProvider
     */
    public function testInvalidAttributeBatchQuery(): void
    {
        $data = [
            [
                'column1' => 1,
                'column2' => true,
                'column3' => null,
            ],
            [
                'column1' => 2,
                'column2' => false,
                'column3' => ['hello'],
            ],
        ];
        $this->expectException(InvalidUpsertArguments::class);
        $this->provider->getUpsertBatchQuery($data, 'test_table');
    }

    public function upsertBatchQueryDataProvider(): array
    {
        return [
            'case 1' => [
                'data' => [
                    [
                        'column1' => 1,
                        'column2' => true,
                        'column3' => null,
                    ],
                    [
                        'column1' => 2,
                        'column2' => false,
                        'column3' => "hello",
                    ],
                ],
                'tableName' => 'test_table',
                'expectedQuery' => 'INSERT INTO test_table (column1, column2, column3) '
                    . 'VALUES (1, 1, NULL), (2, 0, \'hello\') '
                    . 'ON DUPLICATE KEY UPDATE column1 = VALUES(column1), column2 = VALUES(column2), column3 = VALUES(column3)',

            ],
        ];
    }

    public function upsertQueryDataProvider(): array
    {
        return [
            'case 1' => [
                'data' => [
                    'column1' => 'value1',
                    'column2' => 'value2',
                    'column3' => 'value3',
                ],
                'tableName' => 'test_table',
                'expectedQuery' => 'INSERT INTO test_table (column1, column2, column3) '
                    . 'VALUES (:column1, :column2, :column3) '
                    . 'ON DUPLICATE KEY UPDATE column1 = VALUES(column1), column2 = VALUES(column2), column3 = VALUES(column3)',
            ],
            'case 2' => [
                'data' => [
                    'columnA' => 'valueA',
                    'columnB' => 'valueB',
                ],
                'tableName' => 'another_table',
                'expectedQuery' => 'INSERT INTO another_table (columnA, columnB) '
                    . 'VALUES (:columnA, :columnB) '
                    . 'ON DUPLICATE KEY UPDATE columnA = VALUES(columnA), columnB = VALUES(columnB)',
            ],
        ];
    }
}
