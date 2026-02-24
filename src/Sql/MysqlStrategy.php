<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql;

use PhpDb\Sql\Ddl\AlterTable;
use PhpDb\Sql\Ddl\CreateTable;
use PhpDb\Sql\Select;
use PhpDb\Sql\Strategy\AbstractSqlStrategy;

final class MysqlStrategy extends AbstractSqlStrategy
{
    public function __construct()
    {
        $this->setTypeDecorator(Select::class, new SelectDecorator());
        $this->setTypeDecorator(CreateTable::class, new Ddl\CreateTableDecorator());
        $this->setTypeDecorator(AlterTable::class, new Ddl\AlterTableDecorator());
    }
}
