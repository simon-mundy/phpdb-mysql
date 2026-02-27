<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl\Column;

use PhpDb\Sql\Ddl\Column\Text;

class LongText extends Text
{
    protected string $type = 'LONGTEXT';
}
