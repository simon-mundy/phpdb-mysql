<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl;

use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Sql\AbstractSql;
use PhpDb\Sql\TableIdentifier;

use function implode;

class CreateIndex extends AbstractSql
{
    protected array $specifications = [
        'index' => 'CREATE %1$s%2$sINDEX %3$s ON %4$s (%5$s)%6$s',
    ];

    protected string $name;

    protected string|TableIdentifier $table;

    /** @var string[] */
    protected array $columns;

    protected ?string $type;

    /** @var int[] */
    protected array $lengths;

    protected bool $unique;

    /**
     * @param string[] $columns
     * @param int[]    $lengths
     */
    public function __construct(
        string $name,
        string|TableIdentifier $table,
        array $columns,
        ?string $type = null,
        array $lengths = [],
        bool $unique = false
    ) {
        $this->name    = $name;
        $this->table   = $table;
        $this->columns = $columns;
        $this->type    = $type;
        $this->lengths = $lengths;
        $this->unique  = $unique;
    }

    /** @return string[] */
    protected function processIndex(?PlatformInterface $adapterPlatform = null): array
    {
        $uniqueFlag = $this->unique ? 'UNIQUE ' : '';
        $typeFlag   = $this->type !== null ? $this->type . ' ' : '';
        $name       = $adapterPlatform->quoteIdentifier($this->name);
        $table      = $this->resolveTable($this->table, $adapterPlatform);

        $columnParts = [];
        foreach ($this->columns as $i => $column) {
            $col = $adapterPlatform->quoteIdentifier($column);
            if (isset($this->lengths[$i])) {
                $col .= '(' . $this->lengths[$i] . ')';
            }
            $columnParts[] = $col;
        }

        return [
            $uniqueFlag,
            $typeFlag,
            $name,
            $table,
            implode(', ', $columnParts),
            '',
        ];
    }
}
