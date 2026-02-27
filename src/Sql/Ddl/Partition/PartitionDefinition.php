<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl\Partition;

use PhpDb\Adapter\Platform\PlatformInterface;

class PartitionDefinition
{
    protected string $name;

    protected ?string $operator;

    protected ?string $value;

    protected ?string $engine;

    protected ?string $comment;

    public function __construct(
        string $name,
        ?string $operator = null,
        ?string $value = null,
        ?string $engine = null,
        ?string $comment = null
    ) {
        $this->name     = $name;
        $this->operator = $operator;
        $this->value    = $value;
        $this->engine   = $engine;
        $this->comment  = $comment;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOperator(): ?string
    {
        return $this->operator;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function getEngine(): ?string
    {
        return $this->engine;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getSql(PlatformInterface $platform): string
    {
        $sql = 'PARTITION ' . $platform->quoteIdentifier($this->name);

        if ($this->operator !== null && $this->value !== null) {
            $sql .= ' VALUES ' . $this->operator . ' (' . $this->value . ')';
        }

        if ($this->engine !== null) {
            $sql .= ' ENGINE = ' . $this->engine;
        }

        if ($this->comment !== null) {
            $sql .= ' COMMENT = ' . $platform->quoteTrustedValue($this->comment);
        }

        return $sql;
    }
}
