<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl;

use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Mysql\Sql\Ddl\Partition\PartitionDefinition;
use PhpDb\Sql\Ddl\AlterTable;
use PhpDb\Sql\Ddl\Column\ColumnInterface;
use PhpDb\Sql\TableIdentifier;

class MysqlAlterTable extends AlterTable
{
    /** @var ColumnInterface[] */
    protected array $modifyColumns = [];

    /** @var array<int, array{old: string, new: string}> */
    protected array $renameColumns = [];

    protected ?string $renameTable = null;

    protected ?string $convertCharset = null;

    protected ?string $convertCollation = null;

    /** @var PartitionDefinition[] */
    protected array $addPartitions = [];

    /** @var string[] */
    protected array $dropPartitions = [];

    public function __construct(string|TableIdentifier $table = '')
    {
        parent::__construct($table);

        $this->specifications['modifyColumns'] = [
            "%1\$s" => [
                [1 => "MODIFY COLUMN %1\$s,\n", 'combinedby' => ''],
            ],
        ];

        $this->specifications['renameColumns'] = [
            "%1\$s" => [
                [2 => "RENAME COLUMN %1\$s TO %2\$s,\n", 'combinedby' => ''],
            ],
        ];

        $this->specifications['renameTable'] = "RENAME TO %1\$s,\n";

        $this->specifications['convertCharacterSet'] = "%1\$s";

        $this->specifications['addPartitions'] = [
            "%1\$s" => [
                [1 => "ADD PARTITION (%1\$s),\n", 'combinedby' => ''],
            ],
        ];

        $this->specifications['dropPartitions'] = [
            "%1\$s" => [
                [1 => "DROP PARTITION %1\$s,\n", 'combinedby' => ''],
            ],
        ];
    }

    public function modifyColumn(ColumnInterface $column): static
    {
        $this->modifyColumns[] = $column;
        return $this;
    }

    public function renameColumn(string $old, string $new): static
    {
        $this->renameColumns[] = ['old' => $old, 'new' => $new];
        return $this;
    }

    public function renameTable(string $newName): static
    {
        $this->renameTable = $newName;
        return $this;
    }

    public function convertToCharacterSet(string $charset, ?string $collation = null): static
    {
        $this->convertCharset   = $charset;
        $this->convertCollation = $collation;
        return $this;
    }

    public function addPartition(PartitionDefinition $definition): static
    {
        $this->addPartitions[] = $definition;
        return $this;
    }

    public function dropPartition(string $name): static
    {
        $this->dropPartitions[] = $name;
        return $this;
    }

    /** @return string[][]|null */
    protected function processModifyColumns(?PlatformInterface $adapterPlatform = null): ?array
    {
        if (! $this->modifyColumns) {
            return null;
        }

        $sqls = [];
        foreach ($this->modifyColumns as $column) {
            $sqls[] = $this->processExpression($column, $adapterPlatform);
        }

        return [$sqls];
    }

    /** @return array<int, list<array<int, string>>>|null */
    protected function processRenameColumns(?PlatformInterface $adapterPlatform = null): ?array
    {
        if (! $this->renameColumns) {
            return null;
        }

        $sqls = [];
        foreach ($this->renameColumns as $rename) {
            $sqls[] = [
                $adapterPlatform->quoteIdentifier($rename['old']),
                $adapterPlatform->quoteIdentifier($rename['new']),
            ];
        }

        return [$sqls];
    }

    /** @return string[]|null */
    protected function processRenameTable(?PlatformInterface $adapterPlatform = null): ?array
    {
        if ($this->renameTable === null) {
            return null;
        }

        return [$adapterPlatform->quoteIdentifier($this->renameTable)];
    }

    protected function processConvertCharacterSet(?PlatformInterface $adapterPlatform = null): ?string
    {
        if ($this->convertCharset === null) {
            return null;
        }

        $sql = 'CONVERT TO CHARACTER SET ' . $this->convertCharset;

        if ($this->convertCollation !== null) {
            $sql .= ' COLLATE ' . $this->convertCollation;
        }

        return $sql . ",\n";
    }

    /** @return string[][]|null */
    protected function processAddPartitions(?PlatformInterface $adapterPlatform = null): ?array
    {
        if (! $this->addPartitions) {
            return null;
        }

        $sqls = [];
        foreach ($this->addPartitions as $definition) {
            $sqls[] = $definition->getSql($adapterPlatform);
        }

        return [$sqls];
    }

    /** @return string[][]|null */
    protected function processDropPartitions(?PlatformInterface $adapterPlatform = null): ?array
    {
        if (! $this->dropPartitions) {
            return null;
        }

        $sqls = [];
        foreach ($this->dropPartitions as $name) {
            $sqls[] = $adapterPlatform->quoteIdentifier($name);
        }

        return [$sqls];
    }
}
