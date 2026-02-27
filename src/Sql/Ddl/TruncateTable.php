<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl;

use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Sql\AbstractSql;
use PhpDb\Sql\TableIdentifier;

class TruncateTable extends AbstractSql
{
    protected array $specifications = [
        'table' => 'TRUNCATE TABLE %1$s',
    ];

    protected string|TableIdentifier $table;

    public function __construct(string|TableIdentifier $table)
    {
        $this->table = $table;
    }

    /** @return string[] */
    protected function processTable(?PlatformInterface $adapterPlatform = null): array
    {
        return [$this->resolveTable($this->table, $adapterPlatform)];
    }
}
