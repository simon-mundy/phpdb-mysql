<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql;

use PhpDb\Sql\Ddl\Column\ColumnInterface;
use PhpDb\Sql\ExpressionInterface;
use PhpDb\Sql\Platform\AbstractSqlRenderer;
use PhpDb\Sql\Select;

use function count;
use function str_replace;
use function strlen;
use function strpos;
use function strtolower;
use function strtoupper;
use function substr_replace;
use function uksort;

final class Platform extends AbstractSqlRenderer
{
    /** @var array<string, int> */
    private array $columnOptionSortOrder = [
        'unsigned'      => 0,
        'zerofill'      => 1,
        'identity'      => 2,
        'serial'        => 2,
        'autoincrement' => 2,
        'comment'       => 3,
        'columnformat'  => 4,
        'format'        => 4,
        'storage'       => 5,
        'after'         => 6,
    ];

    public function __construct()
    {
        $this->setTypeDecorator(Select::class, new SelectDecorator());
    }

    public function render(ExpressionInterface $expression, ?string $paramPrefix = null): string
    {
        $sql = parent::render($expression, $paramPrefix);

        if ($expression instanceof ColumnInterface) {
            $sql = $this->applyColumnOptions($expression, $sql);
        }

        return $sql;
    }

    private function applyColumnOptions(ColumnInterface $column, string $sql): string
    {
        $columnOptions = $column->getOptions();

        if ($columnOptions === []) {
            return $sql;
        }

        $insertStart = $this->getSqlInsertOffsets($sql);

        uksort($columnOptions, $this->compareColumnOptions(...));

        foreach ($columnOptions as $coName => $coValue) {
            $insert = '';

            if (! $coValue) {
                continue;
            }

            switch ($this->normalizeColumnOption($coName)) {
                case 'unsigned':
                    $insert = ' UNSIGNED';
                    $j      = 0;
                    break;
                case 'zerofill':
                    $insert = ' ZEROFILL';
                    $j      = 0;
                    break;
                case 'identity':
                case 'serial':
                case 'autoincrement':
                    $insert = ' AUTO_INCREMENT';
                    $j      = 1;
                    break;
                case 'comment':
                    $insert = ' COMMENT ' . $this->platform->quoteValue($coValue);
                    $j      = 2;
                    break;
                case 'columnformat':
                case 'format':
                    $insert = ' COLUMN_FORMAT ' . strtoupper($coValue);
                    $j      = 2;
                    break;
                case 'storage':
                    $insert = ' STORAGE ' . strtoupper($coValue);
                    $j      = 2;
                    break;
                case 'after':
                    $insert = ' AFTER ' . $this->platform->quoteIdentifier($coValue);
                    $j      = 2;
                    break;
            }

            if ($insert) {
                $j                = $j ?? 0;
                $sql              = substr_replace($sql, $insert, $insertStart[$j], 0);
                $insertStartCount = count($insertStart);
                for (; $j < $insertStartCount; ++$j) {
                    $insertStart[$j] += strlen($insert);
                }
            }
        }

        return $sql;
    }

    /** @return array<int, int> */
    private function getSqlInsertOffsets(string $sql): array
    {
        $sqlLength   = strlen($sql);
        $insertStart = [];

        foreach (['NOT NULL', 'NULL', 'DEFAULT', 'UNIQUE', 'PRIMARY', 'REFERENCES'] as $needle) {
            $insertPos = strpos($sql, ' ' . $needle);

            if ($insertPos !== false) {
                switch ($needle) {
                    case 'REFERENCES':
                        $insertStart[2] = ! isset($insertStart[2]) ? $insertPos : $insertStart[2];
                        // no break
                    case 'PRIMARY':
                    case 'UNIQUE':
                        $insertStart[1] = ! isset($insertStart[1]) ? $insertPos : $insertStart[1];
                        // no break
                    default:
                        $insertStart[0] = ! isset($insertStart[0]) ? $insertPos : $insertStart[0];
                }
            }
        }

        for ($i = 0; $i <= 3; $i++) {
            $insertStart[$i] = $insertStart[$i] ?? $sqlLength;
        }

        return $insertStart;
    }

    private function normalizeColumnOption(string $name): string
    {
        return strtolower(str_replace(['-', '_', ' '], '', $name));
    }

    private function compareColumnOptions(string $columnA, string $columnB): int
    {
        $columnA = $this->normalizeColumnOption($columnA);
        $columnA = $this->columnOptionSortOrder[$columnA] ?? count($this->columnOptionSortOrder);

        $columnB = $this->normalizeColumnOption($columnB);
        $columnB = $this->columnOptionSortOrder[$columnB] ?? count($this->columnOptionSortOrder);

        return $columnA - $columnB;
    }
}
