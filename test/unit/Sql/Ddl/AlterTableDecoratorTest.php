<?php

declare(strict_types=1);

namespace PhpDbTest\Mysql\Sql\Ddl;

use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Mysql\AdapterPlatform;
use PhpDb\Mysql\Sql\Ddl\AlterTableDecorator;
use PhpDb\Sql\Ddl\AlterTable;
use PhpDb\Sql\Ddl\Column\Column;
use PhpDb\Sql\Ddl\Constraint\Check;
use PhpDb\Sql\Ddl\Constraint\ForeignKey;
use PhpDb\Sql\Ddl\Constraint\PrimaryKey;
use PhpDb\Sql\Ddl\Constraint\UniqueKey;
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

    public function testAddColumnWithFirst(): void
    {
        $at  = new AlterTable('foo');
        $col = new Column('new_col');
        $col->setOption('first', true);
        $at->addColumn($col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('ADD COLUMN', $sql);
        self::assertStringContainsString('FIRST', $sql);
    }

    public function testChangeColumnWithFirst(): void
    {
        $at  = new AlterTable('foo');
        $col = new Column('name');
        $col->setOption('first', true);
        $at->changeColumn('name', $col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CHANGE COLUMN', $sql);
        self::assertStringContainsString('FIRST', $sql);
    }

    public function testChangeColumnWithAfter(): void
    {
        $at  = new AlterTable('foo');
        $col = new Column('email');
        $col->setOption('after', 'name');
        $at->changeColumn('email', $col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CHANGE COLUMN', $sql);
        self::assertStringContainsString('AFTER `name`', $sql);
    }

    public function testDropColumn(): void
    {
        $at = new AlterTable('foo');
        $at->dropColumn('old_col');

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('DROP COLUMN', $sql);
        self::assertStringContainsString('`old_col`', $sql);
    }

    public function testDropConstraint(): void
    {
        $at = new AlterTable('foo');
        $at->dropConstraint('uq_email');

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('DROP CONSTRAINT', $sql);
        self::assertStringContainsString('`uq_email`', $sql);
    }

    public function testDropIndex(): void
    {
        $at = new AlterTable('foo');
        $at->dropIndex('idx_name');

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('DROP INDEX', $sql);
        self::assertStringContainsString('`idx_name`', $sql);
    }

    public function testAddForeignKeyConstraint(): void
    {
        $at = new AlterTable('orders');
        $at->addConstraint(new ForeignKey(
            'fk_user',
            'user_id',
            'users',
            'id',
            'CASCADE',
            'SET NULL'
        ));

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('FOREIGN KEY', $sql);
        self::assertStringContainsString('REFERENCES', $sql);
        self::assertStringContainsString('ON DELETE CASCADE', $sql);
        self::assertStringContainsString('ON UPDATE SET NULL', $sql);
    }

    public function testAddCheckConstraint(): void
    {
        $at = new AlterTable('products');
        $at->addConstraint(new Check('price > 0', 'chk_price'));

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CHECK', $sql);
        self::assertStringContainsString('price > 0', $sql);
    }

    public function testAddUniqueKeyConstraint(): void
    {
        $at = new AlterTable('users');
        $at->addConstraint(new UniqueKey('email', 'uq_email'));

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('UNIQUE', $sql);
    }

    public function testAddPrimaryKeyConstraint(): void
    {
        $at = new AlterTable('foo');
        $at->addConstraint(new PrimaryKey('id'));

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('PRIMARY KEY', $sql);
    }

    public function testTableOptionCompression(): void
    {
        $at = new AlterTable('foo');
        $at->setOption('compression', 'lz4');

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('COMPRESSION = ', $sql);
        self::assertStringContainsString('LZ4', $sql);
    }

    public function testTableOptionEncryption(): void
    {
        $at = new AlterTable('foo');
        $at->setOption('encryption', 'n');

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('ENCRYPTION = ', $sql);
        self::assertStringContainsString('N', $sql);
    }

    public function testAddColumnWithStorage(): void
    {
        $at  = new AlterTable('foo');
        $col = new Column('data');
        $col->setOption('storage', 'disk');
        $at->addColumn($col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('STORAGE DISK', $sql);
    }

    public function testAddColumnWithColumnFormat(): void
    {
        $at  = new AlterTable('foo');
        $col = new Column('data');
        $col->setOption('columnformat', 'fixed');
        $at->addColumn($col);

        $decorator = $this->decorate($at);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('COLUMN_FORMAT FIXED', $sql);
    }
}
