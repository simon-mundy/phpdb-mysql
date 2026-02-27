<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl\Column;

use Override;
use PhpDb\Sql\Argument\Identifier;
use PhpDb\Sql\Ddl\Column\Column;

use function implode;

class GeneratedColumn extends Column
{
    protected string $expression;

    protected string $storage;

    public function __construct(
        string $name,
        string $type,
        string $expression,
        string $storage = 'VIRTUAL',
        bool $nullable = false,
        array $options = []
    ) {
        $this->type       = $type;
        $this->expression = $expression;
        $this->storage    = $storage;

        parent::__construct($name, $nullable, null, $options);
    }

    public function getExpression(): string
    {
        return $this->expression;
    }

    public function getStorage(): string
    {
        return $this->storage;
    }

    /** @inheritDoc */
    #[Override]
    public function getExpressionData(): array
    {
        $specParts = ['%s ' . $this->type . ' GENERATED ALWAYS AS (' . $this->expression . ') ' . $this->storage];
        $values    = [new Identifier($this->name)];

        if ($this->isNullable === false) {
            $specParts[] = 'NOT NULL';
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
