<?php

declare(strict_types=1);

namespace PhpDbTest\Mysql\Sql\Ddl\Column;

use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Mysql\AdapterPlatform;
use PhpDb\Mysql\Sql\Ddl\Column\Bit;
use PhpDb\Mysql\Sql\Ddl\Column\Enum;
use PhpDb\Mysql\Sql\Ddl\Column\Geometry;
use PhpDb\Mysql\Sql\Ddl\Column\GeometryCollection;
use PhpDb\Mysql\Sql\Ddl\Column\LineString;
use PhpDb\Mysql\Sql\Ddl\Column\LongBlob;
use PhpDb\Mysql\Sql\Ddl\Column\LongText;
use PhpDb\Mysql\Sql\Ddl\Column\MediumBlob;
use PhpDb\Mysql\Sql\Ddl\Column\MediumInteger;
use PhpDb\Mysql\Sql\Ddl\Column\MediumText;
use PhpDb\Mysql\Sql\Ddl\Column\MultiLineString;
use PhpDb\Mysql\Sql\Ddl\Column\MultiPoint;
use PhpDb\Mysql\Sql\Ddl\Column\MultiPolygon;
use PhpDb\Mysql\Sql\Ddl\Column\Point;
use PhpDb\Mysql\Sql\Ddl\Column\Polygon;
use PhpDb\Mysql\Sql\Ddl\Column\Set;
use PhpDb\Mysql\Sql\Ddl\Column\TinyBlob;
use PhpDb\Mysql\Sql\Ddl\Column\TinyInteger;
use PhpDb\Mysql\Sql\Ddl\Column\TinyText;
use PhpDb\Mysql\Sql\Ddl\Column\Year;
use PhpDb\Mysql\Sql\Ddl\CreateTableDecorator;
use PhpDb\Sql\Ddl\CreateTable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TinyInteger::class)]
#[CoversClass(MediumInteger::class)]
#[CoversClass(TinyText::class)]
#[CoversClass(MediumText::class)]
#[CoversClass(LongText::class)]
#[CoversClass(TinyBlob::class)]
#[CoversClass(MediumBlob::class)]
#[CoversClass(LongBlob::class)]
#[CoversClass(Enum::class)]
#[CoversClass(Set::class)]
#[CoversClass(Year::class)]
#[CoversClass(Bit::class)]
#[CoversClass(Geometry::class)]
#[CoversClass(Point::class)]
#[CoversClass(LineString::class)]
#[CoversClass(Polygon::class)]
#[CoversClass(MultiPoint::class)]
#[CoversClass(MultiLineString::class)]
#[CoversClass(MultiPolygon::class)]
#[CoversClass(GeometryCollection::class)]
final class ColumnTypesTest extends TestCase
{
    private AdapterPlatform $platform;

    protected function setUp(): void
    {
        $driver         = $this->createMock(DriverInterface::class);
        $this->platform = new AdapterPlatform($driver);
    }

    private function getColumnSql(object $column): string
    {
        $ct = new CreateTable('test');
        $ct->addColumn($column);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);

        return $decorator->getSqlString($this->platform);
    }

    public function testTinyInteger(): void
    {
        $sql = $this->getColumnSql(new TinyInteger('status'));
        self::assertStringContainsString('TINYINT', $sql);
        self::assertStringContainsString('`status`', $sql);
    }

    public function testMediumInteger(): void
    {
        $sql = $this->getColumnSql(new MediumInteger('counter'));
        self::assertStringContainsString('MEDIUMINT', $sql);
        self::assertStringContainsString('`counter`', $sql);
    }

    public function testTinyText(): void
    {
        $sql = $this->getColumnSql(new TinyText('note'));
        self::assertStringContainsString('TINYTEXT', $sql);
        self::assertStringContainsString('`note`', $sql);
    }

    public function testMediumText(): void
    {
        $sql = $this->getColumnSql(new MediumText('content'));
        self::assertStringContainsString('MEDIUMTEXT', $sql);
        self::assertStringContainsString('`content`', $sql);
    }

    public function testLongText(): void
    {
        $sql = $this->getColumnSql(new LongText('body'));
        self::assertStringContainsString('LONGTEXT', $sql);
        self::assertStringContainsString('`body`', $sql);
    }

    public function testTinyBlob(): void
    {
        $sql = $this->getColumnSql(new TinyBlob('thumb'));
        self::assertStringContainsString('TINYBLOB', $sql);
        self::assertStringContainsString('`thumb`', $sql);
    }

    public function testMediumBlob(): void
    {
        $sql = $this->getColumnSql(new MediumBlob('image'));
        self::assertStringContainsString('MEDIUMBLOB', $sql);
        self::assertStringContainsString('`image`', $sql);
    }

    public function testLongBlob(): void
    {
        $sql = $this->getColumnSql(new LongBlob('video'));
        self::assertStringContainsString('LONGBLOB', $sql);
        self::assertStringContainsString('`video`', $sql);
    }

    public function testYear(): void
    {
        $sql = $this->getColumnSql(new Year('birth_year'));
        self::assertStringContainsString('YEAR', $sql);
        self::assertStringContainsString('`birth_year`', $sql);
    }

    public function testBit(): void
    {
        $sql = $this->getColumnSql(new Bit('flags', 8));
        self::assertStringContainsString('BIT', $sql);
        self::assertStringContainsString('`flags`', $sql);
    }

    public function testBitWithoutLength(): void
    {
        $sql = $this->getColumnSql(new Bit('active'));
        self::assertStringContainsString('BIT', $sql);
        self::assertStringContainsString('`active`', $sql);
    }

    public function testEnum(): void
    {
        $col = new Enum('status', ['active', 'inactive', 'pending']);
        $sql = $this->getColumnSql($col);

        self::assertStringContainsString("ENUM('active','inactive','pending')", $sql);
        self::assertStringContainsString('`status`', $sql);
    }

    public function testEnumGetValues(): void
    {
        $col = new Enum('status', ['active', 'inactive']);
        self::assertSame(['active', 'inactive'], $col->getValues());
    }

    public function testEnumNullable(): void
    {
        $col = new Enum('status', ['active', 'inactive'], true);
        $sql = $this->getColumnSql($col);

        self::assertStringContainsString("ENUM('active','inactive')", $sql);
        self::assertStringNotContainsString('NOT NULL', $sql);
    }

    public function testEnumWithDefault(): void
    {
        $col = new Enum('status', ['active', 'inactive'], false, 'active');
        $sql = $this->getColumnSql($col);

        self::assertStringContainsString("ENUM('active','inactive')", $sql);
        self::assertStringContainsString('DEFAULT', $sql);
    }

    public function testSet(): void
    {
        $col = new Set('permissions', ['read', 'write', 'execute']);
        $sql = $this->getColumnSql($col);

        self::assertStringContainsString("SET('read','write','execute')", $sql);
        self::assertStringContainsString('`permissions`', $sql);
    }

    public function testSetGetValues(): void
    {
        $col = new Set('perms', ['read', 'write']);
        self::assertSame(['read', 'write'], $col->getValues());
    }

    public function testSetNullable(): void
    {
        $col = new Set('perms', ['read', 'write'], true);
        $sql = $this->getColumnSql($col);

        self::assertStringContainsString("SET('read','write')", $sql);
        self::assertStringNotContainsString('NOT NULL', $sql);
    }

    public function testGeometry(): void
    {
        $sql = $this->getColumnSql(new Geometry('shape'));
        self::assertStringContainsString('GEOMETRY', $sql);
        self::assertStringContainsString('`shape`', $sql);
    }

    public function testPoint(): void
    {
        $sql = $this->getColumnSql(new Point('location'));
        self::assertStringContainsString('POINT', $sql);
        self::assertStringContainsString('`location`', $sql);
    }

    public function testLineString(): void
    {
        $sql = $this->getColumnSql(new LineString('path'));
        self::assertStringContainsString('LINESTRING', $sql);
        self::assertStringContainsString('`path`', $sql);
    }

    public function testPolygon(): void
    {
        $sql = $this->getColumnSql(new Polygon('area'));
        self::assertStringContainsString('POLYGON', $sql);
        self::assertStringContainsString('`area`', $sql);
    }

    public function testMultiPoint(): void
    {
        $sql = $this->getColumnSql(new MultiPoint('locations'));
        self::assertStringContainsString('MULTIPOINT', $sql);
        self::assertStringContainsString('`locations`', $sql);
    }

    public function testMultiLineString(): void
    {
        $sql = $this->getColumnSql(new MultiLineString('routes'));
        self::assertStringContainsString('MULTILINESTRING', $sql);
        self::assertStringContainsString('`routes`', $sql);
    }

    public function testMultiPolygon(): void
    {
        $sql = $this->getColumnSql(new MultiPolygon('regions'));
        self::assertStringContainsString('MULTIPOLYGON', $sql);
        self::assertStringContainsString('`regions`', $sql);
    }

    public function testGeometryCollection(): void
    {
        $sql = $this->getColumnSql(new GeometryCollection('shapes'));
        self::assertStringContainsString('GEOMETRYCOLLECTION', $sql);
        self::assertStringContainsString('`shapes`', $sql);
    }

    public function testTinyIntegerWithOptions(): void
    {
        $col = new TinyInteger('active');
        $col->setOption('unsigned', true);
        $sql = $this->getColumnSql($col);

        self::assertStringContainsString('TINYINT', $sql);
        self::assertStringContainsString('UNSIGNED', $sql);
    }

    public function testMediumIntegerWithOptions(): void
    {
        $col = new MediumInteger('counter');
        $col->setOption('unsigned', true);
        $col->setOption('zerofill', true);
        $sql = $this->getColumnSql($col);

        self::assertStringContainsString('MEDIUMINT', $sql);
        self::assertStringContainsString('UNSIGNED', $sql);
        self::assertStringContainsString('ZEROFILL', $sql);
    }

    public function testLongTextWithCharsetAndCollation(): void
    {
        $col = new LongText('content');
        $col->setOption('charset', 'utf8mb4');
        $col->setOption('collation', 'utf8mb4_unicode_ci');
        $sql = $this->getColumnSql($col);

        self::assertStringContainsString('LONGTEXT', $sql);
        self::assertStringContainsString('CHARACTER SET utf8mb4', $sql);
        self::assertStringContainsString('COLLATE utf8mb4_unicode_ci', $sql);
    }

    public function testPointNotNull(): void
    {
        $col = new Point('location', false);
        $sql = $this->getColumnSql($col);

        self::assertStringContainsString('POINT', $sql);
        self::assertStringContainsString('NOT NULL', $sql);
    }

    public function testPointNullable(): void
    {
        $col = new Point('location', true);
        $sql = $this->getColumnSql($col);

        self::assertStringContainsString('POINT', $sql);
        self::assertStringNotContainsString('NOT NULL', $sql);
    }
}
