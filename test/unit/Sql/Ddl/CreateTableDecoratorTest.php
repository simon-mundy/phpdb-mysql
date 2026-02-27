<?php

declare(strict_types=1);

namespace PhpDbTest\Mysql\Sql\Ddl;

use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Mysql\AdapterPlatform;
use PhpDb\Mysql\Sql\Ddl\Column\Enum;
use PhpDb\Mysql\Sql\Ddl\Column\LongText;
use PhpDb\Mysql\Sql\Ddl\Column\MediumInteger;
use PhpDb\Mysql\Sql\Ddl\Column\Point;
use PhpDb\Mysql\Sql\Ddl\Column\TinyInteger;
use PhpDb\Mysql\Sql\Ddl\CreateTableDecorator;
use PhpDb\Mysql\Sql\Ddl\Index\FulltextIndex;
use PhpDb\Mysql\Sql\Ddl\Index\SpatialIndex;
use PhpDb\Sql\Ddl\Column\Column;
use PhpDb\Sql\Ddl\Column\Varchar;
use PhpDb\Sql\Ddl\Constraint\Check;
use PhpDb\Sql\Ddl\Constraint\ForeignKey;
use PhpDb\Sql\Ddl\Constraint\PrimaryKey;
use PhpDb\Sql\Ddl\Constraint\UniqueKey;
use PhpDb\Sql\Ddl\CreateTable;
use PhpDb\Sql\Ddl\Index\Index;
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

    public function testTableOptionCompression(): void
    {
        $ct = new CreateTable('foo');
        $ct->addColumn(new Column('id'));
        $ct->setOption('compression', 'zlib');

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('COMPRESSION = ', $sql);
        self::assertStringContainsString('ZLIB', $sql);
    }

    public function testTableOptionEncryption(): void
    {
        $ct = new CreateTable('foo');
        $ct->addColumn(new Column('id'));
        $ct->setOption('encryption', 'y');

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('ENCRYPTION = ', $sql);
        self::assertStringContainsString('Y', $sql);
    }

    public function testForeignKeyConstraint(): void
    {
        $ct = new CreateTable('orders');
        $ct->addColumn(new Column('id'));
        $ct->addColumn(new Column('user_id'));
        $ct->addConstraint(new ForeignKey(
            'fk_user',
            'user_id',
            'users',
            'id',
            'CASCADE',
            'SET NULL'
        ));

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('FOREIGN KEY', $sql);
        self::assertStringContainsString('REFERENCES', $sql);
        self::assertStringContainsString('ON DELETE CASCADE', $sql);
        self::assertStringContainsString('ON UPDATE SET NULL', $sql);
    }

    public function testCheckConstraint(): void
    {
        $ct = new CreateTable('products');
        $ct->addColumn(new Column('price'));
        $ct->addConstraint(new Check('price > 0', 'chk_price'));

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CHECK', $sql);
        self::assertStringContainsString('price > 0', $sql);
    }

    public function testUniqueKeyConstraint(): void
    {
        $ct = new CreateTable('users');
        $ct->addColumn(new Column('email'));
        $ct->addConstraint(new UniqueKey('email', 'uq_email'));

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('UNIQUE', $sql);
    }

    public function testTemporaryTable(): void
    {
        $ct = new CreateTable('tmp_data', true);
        $ct->addColumn(new Column('id'));

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CREATE TEMPORARY TABLE', $sql);
    }

    public function testTemporaryTableWithIfNotExists(): void
    {
        $ct = new CreateTable('tmp_data', true);
        $ct->ifNotExists();
        $ct->addColumn(new Column('id'));

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CREATE TEMPORARY TABLE', $sql);
        self::assertStringContainsString('IF NOT EXISTS', $sql);
    }

    public function testMysqlColumnTypesInCreateTable(): void
    {
        $ct = new CreateTable('mixed');
        $ct->addColumn(new TinyInteger('status'));
        $ct->addColumn(new MediumInteger('counter'));
        $ct->addColumn(new LongText('body'));
        $ct->addColumn(new Point('location'));
        $ct->addColumn(new Enum('role', ['admin', 'user']));

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('TINYINT', $sql);
        self::assertStringContainsString('MEDIUMINT', $sql);
        self::assertStringContainsString('LONGTEXT', $sql);
        self::assertStringContainsString('POINT', $sql);
        self::assertStringContainsString("ENUM('admin','user')", $sql);
    }

    public function testFullTableWithAllFeatures(): void
    {
        $ct = new CreateTable('articles');

        $id = new Column('id');
        $id->setOption('unsigned', true);
        $id->setOption('autoincrement', true);
        $ct->addColumn($id);

        $ct->addColumn(new Varchar('title', 255));

        $body = new LongText('body');
        $body->setOption('charset', 'utf8mb4');
        $ct->addColumn($body);

        $ct->addColumn(new Enum('status', ['draft', 'published']));

        $ct->addConstraint(new PrimaryKey('id'));
        $ct->addConstraint(new UniqueKey('title', 'uq_title'));
        $ct->addConstraint(new FulltextIndex('body', 'ft_body'));
        $ct->addConstraint(new Check('LENGTH(title) > 0', 'chk_title'));

        $ct->setOption('engine', 'InnoDB');
        $ct->setOption('charset', 'utf8mb4');
        $ct->setOption('collate', 'utf8mb4_unicode_ci');
        $ct->setOption('comment', 'Article storage');

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('AUTO_INCREMENT', $sql);
        self::assertStringContainsString('UNSIGNED', $sql);
        self::assertStringContainsString('VARCHAR', $sql);
        self::assertStringContainsString('LONGTEXT', $sql);
        self::assertStringContainsString('CHARACTER SET utf8mb4', $sql);
        self::assertStringContainsString("ENUM('draft','published')", $sql);
        self::assertStringContainsString('PRIMARY KEY', $sql);
        self::assertStringContainsString('UNIQUE', $sql);
        self::assertStringContainsString('FULLTEXT INDEX', $sql);
        self::assertStringContainsString('CHECK', $sql);
        self::assertStringContainsString('ENGINE = InnoDB', $sql);
        self::assertStringContainsString('DEFAULT CHARACTER SET = utf8mb4', $sql);
        self::assertStringContainsString('COLLATE = utf8mb4_unicode_ci', $sql);
        self::assertStringContainsString('COMMENT = ', $sql);
    }

    public function testIndexInCreateTable(): void
    {
        $ct = new CreateTable('foo');
        $ct->addColumn(new Column('name'));
        $ct->addConstraint(new Index('name', 'idx_name'));

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('INDEX', $sql);
    }

    public function testSpatialIndexInCreateTable(): void
    {
        $ct = new CreateTable('geo');
        $ct->addColumn(new Point('location'));
        $ct->addConstraint(new SpatialIndex('location', 'sp_location'));

        $decorator = $this->decorate($ct);
        $sql       = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('SPATIAL INDEX', $sql);
    }
}
