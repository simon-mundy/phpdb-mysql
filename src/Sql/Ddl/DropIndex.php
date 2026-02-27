<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl;

use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Sql\AbstractSql;
use PhpDb\Sql\TableIdentifier;

class DropIndex extends AbstractSql
{
    protected array $specifications = [
        'index' => 'DROP INDEX %1$s ON %2$s',
    ];

    protected string $name;

    protected string|TableIdentifier $table;

    public function __construct(string $name, string|TableIdentifier $table)
    {
        $this->name  = $name;
        $this->table = $table;
    }

    /** @return string[] */
    protected function processIndex(?PlatformInterface $adapterPlatform = null): array
    {
        return [
            $adapterPlatform->quoteIdentifier($this->name),
            $this->resolveTable($this->table, $adapterPlatform),
        ];
    }
}
