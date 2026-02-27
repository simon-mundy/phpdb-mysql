<?php

declare(strict_types=1);

namespace PhpDbTest\Mysql\Sql\Ddl;

use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Mysql\AdapterPlatform;
use PhpDb\Mysql\Sql\Ddl\AlterTableDecorator;
use PhpDb\Mysql\Sql\Ddl\MysqlAlterTable;
use PhpDb\Mysql\Sql\Ddl\Partition\PartitionDefinition;
use PhpDb\Sql\Ddl\Column\Column;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MysqlAlterTable::class)]
#[CoversClass(AlterTableDecorator::class)]
final class MysqlAlterTableTest extends TestCase
{
    private AdapterPlatform $platform;

    protected function setUp(): void
    {
        $driver         = $this->createMock(DriverInterface::class);
        $this->platform = new AdapterPlatform($driver);
    }

    private function decorate(MysqlAlterTable $alterTable): AlterTableDecorator
    {
        $decorator = new AlterTableDecorator();
        $decorator->setSubject($alterTable);

        return $decorator;
    }

    public function testModifyColumn(): void
    {
        $at  = new MysqlAlterTable('foo');
        $col = new Column('age');
        $at->modifyColumn($col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('MODIFY COLUMN', $sql);
        self::assertStringContainsString('`age`', $sql);
    }

    public function testModifyColumnWithOptions(): void
    {
        $at  = new MysqlAlterTable('foo');
        $col = new Column('age');
        $col->setOption('unsigned', true);
        $col->setOption('comment', 'User age');
        $at->modifyColumn($col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('MODIFY COLUMN', $sql);
        self::assertStringContainsString('UNSIGNED', $sql);
        self::assertStringContainsString('COMMENT', $sql);
    }

    public function testRenameColumn(): void
    {
        $at = new MysqlAlterTable('foo');
        $at->renameColumn('old_name', 'new_name');

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('RENAME COLUMN', $sql);
        self::assertStringContainsString('`old_name`', $sql);
        self::assertStringContainsString('TO', $sql);
        self::assertStringContainsString('`new_name`', $sql);
    }

    public function testRenameTable(): void
    {
        $at = new MysqlAlterTable('foo');
        $at->renameTable('bar');

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('RENAME TO', $sql);
        self::assertStringContainsString('`bar`', $sql);
    }

    public function testConvertToCharacterSet(): void
    {
        $at = new MysqlAlterTable('foo');
        $at->convertToCharacterSet('utf8mb4');

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CONVERT TO CHARACTER SET utf8mb4', $sql);
    }

    public function testConvertToCharacterSetWithCollation(): void
    {
        $at = new MysqlAlterTable('foo');
        $at->convertToCharacterSet('utf8mb4', 'utf8mb4_unicode_ci');

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CONVERT TO CHARACTER SET utf8mb4', $sql);
        self::assertStringContainsString('COLLATE utf8mb4_unicode_ci', $sql);
    }

    public function testAddPartition(): void
    {
        $at  = new MysqlAlterTable('foo');
        $def = new PartitionDefinition('p4', 'LESS THAN', '2025');
        $at->addPartition($def);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('ADD PARTITION', $sql);
        self::assertStringContainsString('LESS THAN', $sql);
        self::assertStringContainsString('2025', $sql);
    }

    public function testDropPartition(): void
    {
        $at = new MysqlAlterTable('foo');
        $at->dropPartition('p0');

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('DROP PARTITION', $sql);
        self::assertStringContainsString('`p0`', $sql);
    }

    public function testAlgorithmOption(): void
    {
        $at  = new MysqlAlterTable('foo');
        $col = new Column('name');
        $at->addColumn($col);
        $at->setOption('algorithm', 'INPLACE');

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('ALGORITHM = INPLACE', $sql);
    }

    public function testLockOption(): void
    {
        $at  = new MysqlAlterTable('foo');
        $col = new Column('name');
        $at->addColumn($col);
        $at->setOption('lock', 'NONE');

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('LOCK = NONE', $sql);
    }

    public function testColumnInvisible(): void
    {
        $at  = new MysqlAlterTable('foo');
        $col = new Column('secret');
        $col->setOption('invisible', true);
        $at->addColumn($col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('INVISIBLE', $sql);
    }

    public function testColumnVisible(): void
    {
        $at  = new MysqlAlterTable('foo');
        $col = new Column('name');
        $col->setOption('visible', true);
        $at->addColumn($col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('VISIBLE', $sql);
    }

    public function testMultipleOperations(): void
    {
        $at = new MysqlAlterTable('foo');
        $at->addColumn(new Column('new_col'));
        $at->renameColumn('old', 'renamed');
        $at->setOption('engine', 'InnoDB');

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('ADD COLUMN', $sql);
        self::assertStringContainsString('RENAME COLUMN', $sql);
        self::assertStringContainsString('ENGINE = InnoDB', $sql);
    }

    public function testModifyColumnWithFirst(): void
    {
        $at  = new MysqlAlterTable('foo');
        $col = new Column('id');
        $col->setOption('first', true);
        $at->modifyColumn($col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('MODIFY COLUMN', $sql);
        self::assertStringContainsString('FIRST', $sql);
    }
}
