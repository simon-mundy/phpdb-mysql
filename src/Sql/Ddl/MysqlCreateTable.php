<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl;

use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Mysql\Sql\Ddl\Partition\Partition;
use PhpDb\Sql\Ddl\CreateTable;
use PhpDb\Sql\TableIdentifier;

class MysqlCreateTable extends CreateTable
{
    protected ?Partition $partition = null;

    public function __construct(string|TableIdentifier $table = '', bool $isTemporary = false)
    {
        parent::__construct($table, $isTemporary);

        $this->specifications['partitioning'] = '%1$s';
    }

    public function setPartition(Partition $partition): static
    {
        $this->partition = $partition;
        return $this;
    }

    public function getPartition(): ?Partition
    {
        return $this->partition;
    }

    /** @return string[]|null */
    protected function processPartitioning(?PlatformInterface $adapterPlatform = null): ?array
    {
        if ($this->partition === null) {
            return null;
        }

        return [$this->partition->getSql($adapterPlatform)];
    }
}
