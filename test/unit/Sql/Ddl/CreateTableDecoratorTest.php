<?php

declare(strict_types=1);

namespace PhpDbTest\Mysql\Sql\Ddl;

use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Mysql\AdapterPlatform;
use PhpDb\Mysql\Sql\Ddl\CreateTableDecorator;
use PhpDb\Sql\Ddl\Column\Column;
use PhpDb\Sql\Ddl\Constraint\PrimaryKey;
use PhpDb\Sql\Ddl\CreateTable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreateTableDecorator::class)]
final class CreateTableDecoratorTest extends TestCase
{
    private AdapterPlatform $platform;

    protected function setUp(): void
    {
        $driver         = $this->createMock(DriverInterface::class);
        $this->platform = new AdapterPlatform($driver);
    }

    private function decorate(CreateTable $createTable): CreateTableDecorator
    {
        $decorator = new CreateTableDecorator();
        $decorator->setSubject($createTable);

        return $decorator;
    }

    public function testTableOptionEngine(): void
    {
        $ct = new CreateTable('foo');
        $ct->addColumn(new Column('id'));
        $ct->setOption('engine', 'InnoDB');

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('ENGINE = InnoDB', $sql);
    }

    public function testTableOptionCharset(): void
    {
        $ct = new CreateTable('foo');
        $ct->addColumn(new Column('id'));
        $ct->setOption('charset', 'utf8mb4');

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('DEFAULT CHARACTER SET = utf8mb4', $sql);
    }

    public function testTableOptionCharacterSet(): void
    {
        $ct = new CreateTable('foo');
        $ct->addColumn(new Column('id'));
        $ct->setOption('character_set', 'utf8mb4');

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('DEFAULT CHARACTER SET = utf8mb4', $sql);
    }

    public function testTableOptionCollate(): void
    {
        $ct = new CreateTable('foo');
        $ct->addColumn(new Column('id'));
        $ct->setOption('collate', 'utf8mb4_unicode_ci');

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('COLLATE = utf8mb4_unicode_ci', $sql);
    }

    public function testTableOptionCollation(): void
    {
        $ct = new CreateTable('foo');
        $ct->addColumn(new Column('id'));
        $ct->setOption('collation', 'utf8mb4_unicode_ci');

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('COLLATE = utf8mb4_unicode_ci', $sql);
    }

    public function testTableOptionComment(): void
    {
        $ct = new CreateTable('foo');
        $ct->addColumn(new Column('id'));
        $ct->setOption('comment', 'My table');

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('COMMENT = ', $sql);
        self::assertStringContainsString('My table', $sql);
    }

    public function testTableOptionAutoIncrement(): void
    {
        $ct = new CreateTable('foo');
        $ct->addColumn(new Column('id'));
        $ct->setOption('auto_increment', 1000);

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('AUTO_INCREMENT = 1000', $sql);
    }

    public function testTableOptionRowFormat(): void
    {
        $ct = new CreateTable('foo');
        $ct->addColumn(new Column('id'));
        $ct->setOption('row_format', 'dynamic');

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('ROW_FORMAT = DYNAMIC', $sql);
    }

    public function testTableOptionKeyBlockSize(): void
    {
        $ct = new CreateTable('foo');
        $ct->addColumn(new Column('id'));
        $ct->setOption('key_block_size', 8);

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('KEY_BLOCK_SIZE = 8', $sql);
    }

    public function testUnrecognizedTableOptionPassesThrough(): void
    {
        $ct = new CreateTable('foo');
        $ct->addColumn(new Column('id'));
        $ct->setOption('custom_opt', 'value');

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CUSTOM_OPT = ', $sql);
    }

    public function testCombinedTableOptions(): void
    {
        $ct = new CreateTable('foo');
        $ct->addColumn(new Column('id'));
        $ct->setOption('engine', 'InnoDB');
        $ct->setOption('charset', 'utf8mb4');
        $ct->setOption('collate', 'utf8mb4_unicode_ci');

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('ENGINE = InnoDB', $sql);
        self::assertStringContainsString('DEFAULT CHARACTER SET = utf8mb4', $sql);
        self::assertStringContainsString('COLLATE = utf8mb4_unicode_ci', $sql);
    }

    public function testColumnOptionUnsigned(): void
    {
        $ct  = new CreateTable('foo');
        $col = new Column('age');
        $col->setOption('unsigned', true);
        $ct->addColumn($col);

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('UNSIGNED', $sql);
    }

    public function testColumnOptionZerofill(): void
    {
        $ct  = new CreateTable('foo');
        $col = new Column('code');
        $col->setOption('zerofill', true);
        $ct->addColumn($col);

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('ZEROFILL', $sql);
    }

    public function testColumnOptionAutoincrement(): void
    {
        $ct  = new CreateTable('foo');
        $col = new Column('id');
        $col->setOption('autoincrement', true);
        $ct->addColumn($col);

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('AUTO_INCREMENT', $sql);
    }

    public function testColumnOptionComment(): void
    {
        $ct  = new CreateTable('foo');
        $col = new Column('name');
        $col->setOption('comment', 'The name');
        $ct->addColumn($col);

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('COMMENT ', $sql);
        self::assertStringContainsString('The name', $sql);
    }

    public function testColumnOptionStorage(): void
    {
        $ct  = new CreateTable('foo');
        $col = new Column('data');
        $col->setOption('storage', 'disk');
        $ct->addColumn($col);

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('STORAGE DISK', $sql);
    }

    public function testColumnOptionColumnFormat(): void
    {
        $ct  = new CreateTable('foo');
        $col = new Column('data');
        $col->setOption('columnformat', 'fixed');
        $ct->addColumn($col);

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('COLUMN_FORMAT FIXED', $sql);
    }

    public function testColumnOptionCharset(): void
    {
        $ct  = new CreateTable('foo');
        $col = new Column('name');
        $col->setOption('charset', 'utf8mb4');
        $ct->addColumn($col);

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CHARACTER SET utf8mb4', $sql);
    }

    public function testColumnOptionCollation(): void
    {
        $ct  = new CreateTable('foo');
        $col = new Column('name');
        $col->setOption('collation', 'utf8mb4_unicode_ci');
        $ct->addColumn($col);

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('COLLATE utf8mb4_unicode_ci', $sql);
    }

    public function testColumnCharsetAndCollationTogether(): void
    {
        $ct  = new CreateTable('foo');
        $col = new Column('name');
        $col->setOption('charset', 'utf8mb4');
        $col->setOption('collation', 'utf8mb4_unicode_ci');
        $ct->addColumn($col);

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CHARACTER SET utf8mb4', $sql);
        self::assertStringContainsString('COLLATE utf8mb4_unicode_ci', $sql);
    }

    public function testIfNotExistsPassthrough(): void
    {
        $ct = new CreateTable('foo');
        $ct->ifNotExists();
        $ct->addColumn(new Column('id'));

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('IF NOT EXISTS', $sql);
    }

    public function testColumnsConstraintsAndTableOptionsTogether(): void
    {
        $ct = new CreateTable('users');
        $ct->addColumn(new Column('id'));
        $ct->addColumn(new Column('name'));
        $ct->addConstraint(new PrimaryKey('id'));
        $ct->setOption('engine', 'InnoDB');
        $ct->setOption('charset', 'utf8mb4');

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CREATE TABLE', $sql);
        self::assertStringContainsString('`id`', $sql);
        self::assertStringContainsString('`name`', $sql);
        self::assertStringContainsString('PRIMARY KEY', $sql);
        self::assertStringContainsString('ENGINE = InnoDB', $sql);
        self::assertStringContainsString('DEFAULT CHARACTER SET = utf8mb4', $sql);
    }
}
