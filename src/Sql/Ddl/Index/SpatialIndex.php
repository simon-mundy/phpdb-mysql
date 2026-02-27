<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl\Index;

use Override;
use PhpDb\Sql\Ddl\Index\Index;

use function implode;
use function str_replace;

class SpatialIndex extends Index
{
    use IndexOptionsTrait;

    protected string $specification = 'SPATIAL INDEX %s(...)';

    /** @inheritDoc */
    #[Override]
    public function getExpressionData(): array
    {
        [$specParts, $values] = $this->buildMysqlColumnSpecs();

        $spec = str_replace('...', implode(', ', $specParts), $this->specification);

        $this->appendMysqlIndexOptions($spec, $values);

        return [
            'spec'   => $spec,
            'values' => $values,
        ];
    }
}
