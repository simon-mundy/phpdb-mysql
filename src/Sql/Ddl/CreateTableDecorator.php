<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl;

use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Sql\Ddl\Column\ColumnInterface;
use PhpDb\Sql\Platform\PlatformDecoratorInterface;
use PhpDb\Sql\PreparableSqlInterface;
use PhpDb\Sql\SqlInterface;

use function count;
use function implode;
use function range;
use function str_replace;
use function strlen;
use function strpos;
use function strtolower;
use function strtoupper;
use function substr_replace;
use function uksort;

final class CreateTableDecorator extends MysqlCreateTable implements PlatformDecoratorInterface
{
    use MysqlTableOptionsTrait;

    protected SqlInterface|PreparableSqlInterface|null $subject;

    /** @var int[] */
    protected $columnOptionSortOrder = [
        'unsigned'      => 0,
        'zerofill'      => 1,
        'identity'      => 2,
        'serial'        => 2,
        'autoincrement' => 2,
        'comment'       => 3,
        'columnformat'  => 4,
        'format'        => 4,
        'storage'       => 5,
        'charset'       => 6,
        'collation'     => 7,
        'invisible'     => 8,
        'visible'       => 8,
    ];

    public function setSubject(
        PreparableSqlInterface|SqlInterface|null $subject
    ): PlatformDecoratorInterface {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param string $sql
     * @return array
     */
    protected function getSqlInsertOffsets($sql)
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

        foreach (range(0, 3) as $i) {
            $insertStart[$i] = $insertStart[$i] ?? $sqlLength;
        }

        return $insertStart;
    }

    /**
     * {@inheritDoc}
     */
    protected function processColumns(?PlatformInterface $platform = null): ?array
    {
        if (! $this->columns) {
            return null;
        }

        $sqls = [];

        foreach ($this->columns as $i => $column) {
            $sql      = $this->processExpression($column, $platform);
            $sqls[$i] = $this->injectMysqlColumnOptions($sql, $column, $platform);
        }

        return [$sqls];
    }

    /**
     * @return string[]|null
     */
    protected function processTableOptions(?PlatformInterface $adapterPlatform = null): ?array
    {
        if (! $this->options) {
            return null;
        }

        return [implode(' ', $this->buildMysqlTableOptions($adapterPlatform))];
    }

    private function injectMysqlColumnOptions(
        string $sql,
        ColumnInterface $column,
        PlatformInterface $platform
    ): string {
        $insertStart   = $this->getSqlInsertOffsets($sql);
        $columnOptions = $column->getOptions();

        uksort($columnOptions, [$this, 'compareColumnOptions']);

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
                    $insert = ' COMMENT ' . $platform->quoteValue($coValue);
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
                case 'charset':
                case 'characterset':
                    $insert = ' CHARACTER SET ' . $coValue;
                    $j      = 0;
                    break;
                case 'collation':
                case 'collate':
                    $insert = ' COLLATE ' . $coValue;
                    $j      = 0;
                    break;
                case 'invisible':
                    $insert = ' INVISIBLE';
                    $j      = 2;
                    break;
                case 'visible':
                    $insert = ' VISIBLE';
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

    /**
     * @param string $name
     * @return string
     */
    private function normalizeColumnOption($name)
    {
        return strtolower(str_replace(['-', '_', ' '], '', $name));
    }

    /**
     * @param string $columnA
     * @param string $columnB
     * @return int
     */
    // phpcs:ignore SlevomatCodingStandard.Classes.UnusedPrivateElements.UnusedMethod
    private function compareColumnOptions($columnA, $columnB)
    {
        $columnA = $this->normalizeColumnOption($columnA);
        $columnA = $this->columnOptionSortOrder[$columnA] ?? count($this->columnOptionSortOrder);

        $columnB = $this->normalizeColumnOption($columnB);
        $columnB = $this->columnOptionSortOrder[$columnB] ?? count($this->columnOptionSortOrder);

        return $columnA - $columnB;
    }
}
