<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl\Index;

use PhpDb\Sql\Argument\Identifier;
use PhpDb\Sql\Argument\Value;
use PhpDb\Sql\ArgumentInterface;

use function count;
use function strtoupper;

trait IndexOptionsTrait
{
    protected ?string $comment = null;

    protected ?bool $visible = null;

    /** @var array<int, string> */
    protected array $directions = [];

    public function setComment(string $comment): static
    {
        $this->comment = $comment;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;
        return $this;
    }

    public function getVisible(): ?bool
    {
        return $this->visible;
    }

    public function setDirection(int $columnIndex, string $direction): static
    {
        $this->directions[$columnIndex] = strtoupper($direction);
        return $this;
    }

    /**
     * @return array{0: string[], 1: ArgumentInterface[]}
     */
    protected function buildMysqlColumnSpecs(): array
    {
        $colCount  = count($this->columns);
        $values    = [new Identifier($this->name)];
        $specParts = [];

        for ($i = 0; $i < $colCount; $i++) {
            $specPart = '%s';
            $values[] = new Identifier($this->columns[$i]);

            if (isset($this->lengths[$i])) {
                $specPart .= '(' . $this->lengths[$i] . ')';
            }

            if (isset($this->directions[$i])) {
                $specPart .= ' ' . $this->directions[$i];
            }

            $specParts[] = $specPart;
        }

        return [$specParts, $values];
    }

    protected function appendMysqlIndexOptions(string &$spec, array &$values): void
    {
        if ($this->comment !== null) {
            $spec    .= ' COMMENT %s';
            $values[] = new Value($this->comment);
        }

        if ($this->visible !== null) {
            $spec .= $this->visible ? ' VISIBLE' : ' INVISIBLE';
        }
    }
}
