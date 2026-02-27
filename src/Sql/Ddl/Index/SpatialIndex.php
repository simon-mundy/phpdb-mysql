<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl\Index;

use PhpDb\Sql\Ddl\Index\Index;

class SpatialIndex extends Index
{
    protected string $specification = 'SPATIAL INDEX %s(...)';
}
