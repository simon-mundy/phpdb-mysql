<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl\Partition;

use PhpDb\Adapter\Platform\PlatformInterface;

use function count;
use function implode;

class Partition
{
    protected string $type;

    protected string $expression;

    protected ?int $count;

    /** @var PartitionDefinition[] */
    protected array $definitions;

    /** @param PartitionDefinition[] $definitions */
    public function __construct(
        string $type,
        string $expression,
        ?int $count = null,
        array $definitions = []
    ) {
        $this->type        = $type;
        $this->expression  = $expression;
        $this->count       = $count;
        $this->definitions = $definitions;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    /** @return PartitionDefinition[] */
    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    public function getSql(PlatformInterface $platform): string
    {
        $sql = 'PARTITION BY ' . $this->type . '(' . $this->expression . ')';

        if (count($this->definitions) > 0) {
            $parts = [];
            foreach ($this->definitions as $def) {
                $parts[] = $def->getSql($platform);
            }
            $sql .= ' (' . implode(', ', $parts) . ')';
        } elseif ($this->count !== null) {
            $sql .= ' PARTITIONS ' . $this->count;
        }

        return $sql;
    }
}
