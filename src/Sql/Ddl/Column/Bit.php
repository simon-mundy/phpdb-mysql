<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl\Column;

use Override;
use PhpDb\Sql\Ddl\Column\AbstractLengthColumn;

class Bit extends AbstractLengthColumn
{
    protected string $type = 'BIT';

    /** @inheritDoc */
    #[Override]
    public function getExpressionData(): array
    {
        if ($this->getLengthExpression() === '' || $this->getLengthExpression() === '0') {
            $this->specification = '%s %s';
        } else {
            $this->specification = '%s %s(%s)';
        }

        return parent::getExpressionData();
    }
}
