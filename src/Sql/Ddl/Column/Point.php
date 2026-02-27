<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl\Column;

use PhpDb\Sql\Ddl\Column\Column;

class Point extends Column
{
    protected string $type = 'POINT';
}
