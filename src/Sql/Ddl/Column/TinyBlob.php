<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl\Column;

use PhpDb\Sql\Ddl\Column\Blob;

class TinyBlob extends Blob
{
    protected string $type = 'TINYBLOB';
}
