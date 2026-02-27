<?php

declare(strict_types=1);

namespace PhpDbTest\Mysql\Sql\Ddl\Index;

use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Mysql\AdapterPlatform;
use PhpDb\Mysql\Sql\Ddl\CreateTableDecorator;
use PhpDb\Mysql\Sql\Ddl\Index\SpatialIndex;
use PhpDb\Sql\Ddl\CreateTable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SpatialIndex::class)]
final class SpatialIndexTest extends TestCase
{
    private AdapterPlatform $platform;

    protected function setUp(): void
    {
        $driver         = $this->createMock(DriverInterface::class);
        $this->platform = new AdapterPlatform($driver);
    }

    public function testBasicSpatialIndex(): void
    {
        $ct = new CreateTable('places');
        $ct->addConstraint(new SpatialIndex('location', 'idx_location'));

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('SPATIAL INDEX', $sql);
        self::assertStringContainsString('`idx_location`', $sql);
        self::assertStringContainsString('`location`', $sql);
    }

    public function testSpatialIndexMultipleColumns(): void
    {
        $index = new SpatialIndex(['lat', 'lng'], 'idx_coords');

        $ct = new CreateTable('places');
        $ct->addConstraint($index);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('SPATIAL INDEX', $sql);
        self::assertStringContainsString('`idx_coords`', $sql);
        self::assertStringContainsString('`lat`', $sql);
        self::assertStringContainsString('`lng`', $sql);
    }
}
