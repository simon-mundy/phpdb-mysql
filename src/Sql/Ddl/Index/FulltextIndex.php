<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl\Index;

use Override;
use PhpDb\Sql\Argument\Identifier;
use PhpDb\Sql\Argument\Literal;
use PhpDb\Sql\Ddl\Index\Index;

use function count;
use function implode;
use function str_replace;

class FulltextIndex extends Index
{
    protected string $specification = 'FULLTEXT INDEX %s(...)';

    protected ?string $parser = null;

    public function setParser(string $parser): static
    {
        $this->parser = $parser;
        return $this;
    }

    public function getParser(): ?string
    {
        return $this->parser;
    }

    /** @inheritDoc */
    #[Override]
    public function getExpressionData(): array
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

            $specParts[] = $specPart;
        }

        $spec = str_replace('...', implode(', ', $specParts), $this->specification);

        if ($this->parser !== null) {
            $spec    .= ' WITH PARSER %s';
            $values[] = new Literal($this->parser);
        }

        return [
            'spec'   => $spec,
            'values' => $values,
        ];
    }
}
