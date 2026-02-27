<?php

declare(strict_types=1);

namespace PhpDbTest\Mysql\Sql\Ddl;

use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Mysql\AdapterPlatform;
use PhpDb\Mysql\Sql\Ddl\AlterTableDecorator;
use PhpDb\Sql\Ddl\AlterTable;
use PhpDb\Sql\Ddl\Column\Column;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AlterTableDecorator::class)]
final class AlterTableDecoratorTest extends TestCase
{
    private AdapterPlatform $platform;

    protected function setUp(): void
    {
        $driver         = $this->createMock(DriverInterface::class);
        $this->platform = new AdapterPlatform($driver);
    }

    private function decorate(AlterTable $alterTable): AlterTableDecorator
    {
        $decorator = new AlterTableDecorator();
        $decorator->setSubject($alterTable);

        return $decorator;
    }

    public function testAddColumnWithUnsigned(): void
    {
        $at  = new AlterTable('foo');
        $col = new Column('age');
        $col->setOption('unsigned', true);
        $at->addColumn($col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('ADD COLUMN', $sql);
        self::assertStringContainsString('UNSIGNED', $sql);
    }

    public function testAddColumnWithAutoincrement(): void
    {
        $at  = new AlterTable('foo');
        $col = new Column('id');
        $col->setOption('autoincrement', true);
        $at->addColumn($col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('AUTO_INCREMENT', $sql);
    }

    public function testAddColumnWithCharset(): void
    {
        $at  = new AlterTable('foo');
        $col = new Column('name');
        $col->setOption('charset', 'utf8mb4');
        $at->addColumn($col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CHARACTER SET utf8mb4', $sql);
    }

    public function testAddColumnWithCollation(): void
    {
        $at  = new AlterTable('foo');
        $col = new Column('name');
        $col->setOption('collation', 'utf8mb4_unicode_ci');
        $at->addColumn($col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('COLLATE utf8mb4_unicode_ci', $sql);
    }

    public function testAddColumnWithCharsetAndCollation(): void
    {
        $at  = new AlterTable('foo');
        $col = new Column('name');
        $col->setOption('charset', 'utf8mb4');
        $col->setOption('collation', 'utf8mb4_unicode_ci');
        $at->addColumn($col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CHARACTER SET utf8mb4', $sql);
        self::assertStringContainsString('COLLATE utf8mb4_unicode_ci', $sql);
    }

    public function testAddColumnWithComment(): void
    {
        $at  = new AlterTable('foo');
        $col = new Column('name');
        $col->setOption('comment', 'User name');
        $at->addColumn($col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('COMMENT ', $sql);
        self::assertStringContainsString('User name', $sql);
    }

    public function testAddColumnWithAfter(): void
    {
        $at  = new AlterTable('foo');
        $col = new Column('email');
        $col->setOption('after', 'name');
        $at->addColumn($col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('AFTER `name`', $sql);
    }

    public function testChangeColumnWithUnsigned(): void
    {
        $at  = new AlterTable('foo');
        $col = new Column('age');
        $col->setOption('unsigned', true);
        $at->changeColumn('age', $col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CHANGE COLUMN', $sql);
        self::assertStringContainsString('UNSIGNED', $sql);
    }

    public function testChangeColumnWithCharset(): void
    {
        $at  = new AlterTable('foo');
        $col = new Column('name');
        $col->setOption('charset', 'utf8mb4');
        $at->changeColumn('name', $col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CHANGE COLUMN', $sql);
        self::assertStringContainsString('CHARACTER SET utf8mb4', $sql);
    }

    public function testChangeColumnWithCollation(): void
    {
        $at  = new AlterTable('foo');
        $col = new Column('name');
        $col->setOption('collation', 'utf8mb4_unicode_ci');
        $at->changeColumn('name', $col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CHANGE COLUMN', $sql);
        self::assertStringContainsString('COLLATE utf8mb4_unicode_ci', $sql);
    }

    public function testTableOptionEngine(): void
    {
        $at = new AlterTable('foo');
        $at->setOption('engine', 'InnoDB');

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('ENGINE = InnoDB', $sql);
    }

    public function testTableOptionCharset(): void
    {
        $at = new AlterTable('foo');
        $at->setOption('charset', 'utf8mb4');

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('DEFAULT CHARACTER SET = utf8mb4', $sql);
    }

    public function testTableOptionCollate(): void
    {
        $at = new AlterTable('foo');
        $at->setOption('collate', 'utf8mb4_unicode_ci');

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('COLLATE = utf8mb4_unicode_ci', $sql);
    }

    public function testTableOptionComment(): void
    {
        $at = new AlterTable('foo');
        $at->setOption('comment', 'My table');

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('COMMENT = ', $sql);
        self::assertStringContainsString('My table', $sql);
    }

    public function testTableOptionAutoIncrement(): void
    {
        $at = new AlterTable('foo');
        $at->setOption('auto_increment', 1000);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('AUTO_INCREMENT = 1000', $sql);
    }

    public function testTableOptionRowFormat(): void
    {
        $at = new AlterTable('foo');
        $at->setOption('row_format', 'dynamic');

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('ROW_FORMAT = DYNAMIC', $sql);
    }

    public function testCombinedTableOptions(): void
    {
        $at = new AlterTable('foo');
        $at->setOption('engine', 'InnoDB');
        $at->setOption('charset', 'utf8mb4');
        $at->setOption('collate', 'utf8mb4_unicode_ci');

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('ENGINE = InnoDB', $sql);
        self::assertStringContainsString('DEFAULT CHARACTER SET = utf8mb4', $sql);
        self::assertStringContainsString('COLLATE = utf8mb4_unicode_ci', $sql);
    }
}
