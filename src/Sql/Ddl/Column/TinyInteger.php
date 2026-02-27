<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl\Column;

use PhpDb\Sql\Ddl\Column\Integer;

class TinyInteger extends Integer
{
    protected string $type = 'TINYINT';
}
