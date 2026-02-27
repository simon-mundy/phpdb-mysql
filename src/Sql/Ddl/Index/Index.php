<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl\Index;

use Override;
use PhpDb\Sql\Argument\Literal;
use PhpDb\Sql\Ddl\Index\Index as BaseIndex;

use function implode;
use function str_replace;

class Index extends BaseIndex
{
    use IndexOptionsTrait;

    /** @inheritDoc */
    #[Override]
    public function getExpressionData(): array
    {
        [$specParts, $values] = $this->buildMysqlColumnSpecs();

        $spec = str_replace('...', implode(', ', $specParts), $this->specification);

        if ($this->type !== null) {
            $spec    .= ' USING %s';
            $values[] = new Literal($this->type);
        }

        $this->appendMysqlIndexOptions($spec, $values);

        return [
            'spec'   => $spec,
            'values' => $values,
        ];
    }
}
