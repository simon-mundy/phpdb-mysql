<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql;

use PhpDb\Sql\Platform\AbstractSqlRenderer;
use PhpDb\Sql\Platform\SqlDecoratorInterface;
use PhpDb\Sql\Select;

use function assert;

final class SelectDecorator implements SqlDecoratorInterface
{
    public function prepare(object $subject, AbstractSqlRenderer $renderer): void
    {
        assert($subject instanceof Select);

        if (
            $subject->getRawState(Select::LIMIT) === null
            && $subject->getRawState(Select::OFFSET) !== null
        ) {
            $subject->limit('18446744073709551615');
        }
    }
}
