<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl\Column;

use Override;
use PhpDb\Sql\Argument\Literal;
use PhpDb\Sql\Ddl\Column\Datetime as BaseDatetime;

class Datetime extends BaseDatetime
{
    protected ?int $fsp = null;

    public function __construct(
        string $name,
        ?int $fsp = null,
        bool $nullable = false,
        mixed $default = null,
        array $options = []
    ) {
        $this->fsp = $fsp;

        parent::__construct($name, $nullable, $default, $options);
    }

    public function getFsp(): ?int
    {
        return $this->fsp;
    }

    /** @inheritDoc */
    #[Override]
    public function getExpressionData(): array
    {
        $expressionData = parent::getExpressionData();

        if ($this->fsp !== null) {
            $expressionData['values'][1] = new Literal('DATETIME(' . $this->fsp . ')');
        }

        return $expressionData;
    }
}
