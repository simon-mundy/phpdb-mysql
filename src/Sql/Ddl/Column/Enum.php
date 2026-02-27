<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl\Column;

use Override;
use PhpDb\Sql\Argument\Identifier;
use PhpDb\Sql\Argument\Value;
use PhpDb\Sql\ArgumentInterface;
use PhpDb\Sql\Ddl\Column\Column;

use function array_map;
use function implode;

class Enum extends Column
{
    /** @var string[] */
    protected array $values;

    /** @param string[] $values */
    public function __construct(
        string $name,
        array $values,
        bool $nullable = false,
        mixed $default = null,
        array $options = []
    ) {
        $this->values = $values;

        parent::__construct($name, $nullable, $default, $options);
    }

    /** @return string[] */
    public function getValues(): array
    {
        return $this->values;
    }

    /** @inheritDoc */
    #[Override]
    public function getExpressionData(): array
    {
        $quotedValues = array_map(
            static fn(string $v): string => "'" . $v . "'",
            $this->values
        );

        $specParts = ['%s ENUM(' . implode(',', $quotedValues) . ')'];
        $values    = [
            new Identifier($this->name),
        ];

        if ($this->isNullable === false) {
            $specParts[] = 'NOT NULL';
        }

        if ($this->default !== null) {
            $specParts[] = 'DEFAULT %s';
            $values[]    = $this->default instanceof ArgumentInterface
                ? $this->default
                : new Value($this->default);
        }

        foreach ($this->constraints as $constraint) {
            $constraintData = $constraint->getExpressionData();
            $specParts[]    = $constraintData['spec'];
            foreach ($constraintData['values'] as $value) {
                $values[] = $value;
            }
        }

        return [
            'spec'   => implode(' ', $specParts),
            'values' => $values,
        ];
    }
}
