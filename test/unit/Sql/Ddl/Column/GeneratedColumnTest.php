<?php

declare(strict_types=1);

namespace PhpDbTest\Mysql\Sql\Ddl\Column;

use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Mysql\AdapterPlatform;
use PhpDb\Mysql\Sql\Ddl\Column\GeneratedColumn;
use PhpDb\Mysql\Sql\Ddl\CreateTableDecorator;
use PhpDb\Sql\Ddl\CreateTable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GeneratedColumn::class)]
final class GeneratedColumnTest extends TestCase
{
    private AdapterPlatform $platform;

    protected function setUp(): void
    {
        $driver         = $this->createMock(DriverInterface::class);
        $this->platform = new AdapterPlatform($driver);
    }

    public function testVirtualGeneratedColumn(): void
    {
        $col = new GeneratedColumn('full_name', 'VARCHAR(255)', "CONCAT(first_name, ' ', last_name)");

        $ct = new CreateTable('users');
        $ct->addColumn($col);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('GENERATED ALWAYS AS', $sql);
        self::assertStringContainsString('VIRTUAL', $sql);
        self::assertStringContainsString('VARCHAR(255)', $sql);
        self::assertStringContainsString("CONCAT(first_name, ' ', last_name)", $sql);
        self::assertStringContainsString('NOT NULL', $sql);
    }

    public function testStoredGeneratedColumn(): void
    {
        $col = new GeneratedColumn('area', 'DOUBLE', 'width * height', 'STORED');

        $ct = new CreateTable('rectangles');
        $ct->addColumn($col);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('GENERATED ALWAYS AS (width * height) STORED', $sql);
        self::assertStringContainsString('DOUBLE', $sql);
    }

    public function testNullableGeneratedColumn(): void
    {
        $col = new GeneratedColumn('computed', 'INT', 'a + b', 'VIRTUAL', true);

        $ct = new CreateTable('test');
        $ct->addColumn($col);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('GENERATED ALWAYS AS (a + b) VIRTUAL', $sql);
        self::assertStringNotContainsString('NOT NULL', $sql);
    }

    public function testGetters(): void
    {
        $col = new GeneratedColumn('col', 'INT', 'a + b', 'STORED');

        self::assertSame('a + b', $col->getExpression());
        self::assertSame('STORED', $col->getStorage());
    }

    public function testNoDefaultClause(): void
    {
        $col = new GeneratedColumn('computed', 'INT', 'a + b');

        $ct = new CreateTable('test');
        $ct->addColumn($col);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringNotContainsString('DEFAULT', $sql);
    }
}
