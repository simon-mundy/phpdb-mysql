<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl\Column;

use PhpDb\Sql\Ddl\Column\Column;

class MultiPoint extends Column
{
    protected string $type = 'MULTIPOINT';
}
