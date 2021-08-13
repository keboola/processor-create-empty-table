<?php

declare(strict_types=1);

namespace Keboola\CreateEmptyTablesProcessor\Tests;

use Generator;
use Keboola\CreateEmptyTablesProcessor\Config;
use Keboola\CreateEmptyTablesProcessor\ConfigDefinition;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class ConfigTest extends TestCase
{
    /** @dataProvider validConfigDataProvider */
    public function testValidConfig(array $configData): void
    {
        $config = new Config($configData, new ConfigDefinition());
        Assert::assertNotEmpty($config->getData());
    }

    /** @dataProvider invalidConfigDataProvider */
    public function testInvalidConfig(array $configData, string $expectedMessage): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage($expectedMessage);
        new Config($configData, new ConfigDefinition());
    }

    public function validConfigDataProvider(): Generator
    {
        yield 'simple-config' => [
            [
                'parameters' => [
                    'tables' => [
                        [
                            'tableName' => 'simple-table',
                            'columns' => [
                                'col_1',
                                'col_2',
                                'col_3',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function invalidConfigDataProvider(): Generator
    {
        yield 'missing-tables' => [
            [
                'parameters' => [],
            ],
            'The child config "tables" under "root.parameters" must be configured.',
        ];

        yield 'empty-tables' => [
            [
                'parameters' => [
                    'tables' => [],
                ],
            ],
            'The path "root.parameters.tables" should have at least 1 element(s) defined.',
        ];

        yield 'missing-table-name' => [
            [
                'parameters' => [
                    'tables' => [
                        [

                        ],
                    ],
                ],
            ],
            'The child config "tableName" under "root.parameters.tables.0" must be configured.',
        ];

        yield 'empty-table-name' => [
            [
                'parameters' => [
                    'tables' => [
                        [
                            'tableName' => '',
                        ],
                    ],
                ],
            ],
            'The path "root.parameters.tables.0.tableName" cannot contain an empty value, but got "".',
        ];

        yield 'missing-table-columns' => [
            [
                'parameters' => [
                    'tables' => [
                        [
                            'tableName' => 'testTable',
                        ],
                    ],
                ],
            ],
            'The child config "columns" under "root.parameters.tables.0" must be configured.',
        ];

        yield 'empty-table-columns' => [
            [
                'parameters' => [
                    'tables' => [
                        [
                            'tableName' => 'testTable',
                            'columns' => [],
                        ],
                    ],
                ],
            ],
            'The path "root.parameters.tables.0.columns" should have at least 1 element(s) defined.',
        ];
    }
}
