<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl\Column;

use PhpDb\Sql\Ddl\Column\Text;

class MediumText extends Text
{
    protected string $type = 'MEDIUMTEXT';
}
