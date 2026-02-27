<?php

declare(strict_types=1);

namespace PhpDbTest\Mysql\Sql\Ddl\Column;

use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Mysql\AdapterPlatform;
use PhpDb\Mysql\Sql\Ddl\Column\Datetime;
use PhpDb\Mysql\Sql\Ddl\Column\Time;
use PhpDb\Mysql\Sql\Ddl\Column\Timestamp;
use PhpDb\Mysql\Sql\Ddl\CreateTableDecorator;
use PhpDb\Sql\Ddl\CreateTable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Datetime::class)]
#[CoversClass(Time::class)]
#[CoversClass(Timestamp::class)]
final class TemporalColumnsTest extends TestCase
{
    private AdapterPlatform $platform;

    protected function setUp(): void
    {
        $driver         = $this->createMock(DriverInterface::class);
        $this->platform = new AdapterPlatform($driver);
    }

    public function testDatetimeWithoutFsp(): void
    {
        $col = new Datetime('created_at');

        $ct = new CreateTable('events');
        $ct->addColumn($col);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('DATETIME', $sql);
        self::assertStringNotContainsString('DATETIME(', $sql);
    }

    public function testDatetimeWithFsp(): void
    {
        $col = new Datetime('created_at', 3);

        $ct = new CreateTable('events');
        $ct->addColumn($col);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('DATETIME(3)', $sql);
    }

    public function testTimeWithoutFsp(): void
    {
        $col = new Time('duration');

        $ct = new CreateTable('tasks');
        $ct->addColumn($col);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('TIME', $sql);
        self::assertStringNotContainsString('TIME(', $sql);
    }

    public function testTimeWithFsp(): void
    {
        $col = new Time('duration', 6);

        $ct = new CreateTable('tasks');
        $ct->addColumn($col);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('TIME(6)', $sql);
    }

    public function testTimestampWithoutFsp(): void
    {
        $col = new Timestamp('updated_at');

        $ct = new CreateTable('records');
        $ct->addColumn($col);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('TIMESTAMP', $sql);
        self::assertStringNotContainsString('TIMESTAMP(', $sql);
    }

    public function testTimestampWithFsp(): void
    {
        $col = new Timestamp('updated_at', 3);

        $ct = new CreateTable('records');
        $ct->addColumn($col);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('TIMESTAMP(3)', $sql);
    }

    public function testTimestampWithFspAndOnUpdate(): void
    {
        $col = new Timestamp('updated_at', 3);
        $col->setOption('on_update', true);

        $ct = new CreateTable('records');
        $ct->addColumn($col);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('TIMESTAMP(3)', $sql);
        self::assertStringContainsString('ON UPDATE CURRENT_TIMESTAMP(3)', $sql);
    }

    public function testTimestampWithOnUpdateWithoutFsp(): void
    {
        $col = new Timestamp('updated_at');
        $col->setOption('on_update', true);

        $ct = new CreateTable('records');
        $ct->addColumn($col);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('TIMESTAMP', $sql);
        self::assertStringContainsString('ON UPDATE CURRENT_TIMESTAMP', $sql);
        self::assertStringNotContainsString('CURRENT_TIMESTAMP(', $sql);
    }

    public function testDatetimeGetFsp(): void
    {
        $col = new Datetime('dt', 3);
        self::assertSame(3, $col->getFsp());

        $col2 = new Datetime('dt');
        self::assertNull($col2->getFsp());
    }

    public function testTimeGetFsp(): void
    {
        $col = new Time('t', 6);
        self::assertSame(6, $col->getFsp());
    }

    public function testTimestampGetFsp(): void
    {
        $col = new Timestamp('ts', 0);
        self::assertSame(0, $col->getFsp());
    }
}
