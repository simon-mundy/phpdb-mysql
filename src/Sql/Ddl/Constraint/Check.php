<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl\Constraint;

use Override;
use PhpDb\Sql\Ddl\Constraint\Check as BaseCheck;
use PhpDb\Sql\ExpressionInterface;

class Check extends BaseCheck
{
    protected ?bool $enforced;

    public function __construct(string|ExpressionInterface $expression, ?string $name = null, ?bool $enforced = null)
    {
        parent::__construct($expression, $name);

        $this->enforced = $enforced;
    }

    public function isEnforced(): ?bool
    {
        return $this->enforced;
    }

    /** @inheritDoc */
    #[Override]
    public function getExpressionData(): array
    {
        $expressionData = parent::getExpressionData();

        if ($this->enforced === true) {
            $expressionData['spec'] .= ' ENFORCED';
        } elseif ($this->enforced === false) {
            $expressionData['spec'] .= ' NOT ENFORCED';
        }

        return $expressionData;
    }
}
