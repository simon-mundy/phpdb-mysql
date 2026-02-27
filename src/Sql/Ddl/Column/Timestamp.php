<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl\Column;

use Override;
use PhpDb\Sql\Argument\Literal;
use PhpDb\Sql\Ddl\Column\Timestamp as BaseTimestamp;

use function count;

class Timestamp extends BaseTimestamp
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
            $expressionData['values'][1] = new Literal('TIMESTAMP(' . $this->fsp . ')');

            // If on_update is set, replace the ON UPDATE CURRENT_TIMESTAMP literal with fsp
            $options = $this->getOptions();
            if (isset($options['on_update'])) {
                $lastIndex                            = count($expressionData['values']) - 1;
                $expressionData['values'][$lastIndex] = new Literal(
                    'ON UPDATE CURRENT_TIMESTAMP(' . $this->fsp . ')'
                );
            }
        }

        return $expressionData;
    }
}
