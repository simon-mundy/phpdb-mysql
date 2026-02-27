<?php

declare(strict_types=1);

namespace PhpDbTest\Mysql\Sql\Ddl\Constraint;

use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Mysql\AdapterPlatform;
use PhpDb\Mysql\Sql\Ddl\Constraint\Check;
use PhpDb\Mysql\Sql\Ddl\CreateTableDecorator;
use PhpDb\Sql\Ddl\Column\Column;
use PhpDb\Sql\Ddl\CreateTable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Check::class)]
final class CheckTest extends TestCase
{
    private AdapterPlatform $platform;

    protected function setUp(): void
    {
        $driver         = $this->createMock(DriverInterface::class);
        $this->platform = new AdapterPlatform($driver);
    }

    public function testCheckWithoutEnforced(): void
    {
        $check = new Check('price > 0', 'chk_price');

        $ct = new CreateTable('products');
        $ct->addColumn(new Column('price'));
        $ct->addConstraint($check);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CHECK (price > 0)', $sql);
        self::assertStringNotContainsString('ENFORCED', $sql);
    }

    public function testCheckEnforced(): void
    {
        $check = new Check('price > 0', 'chk_price', true);

        $ct = new CreateTable('products');
        $ct->addColumn(new Column('price'));
        $ct->addConstraint($check);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CHECK (price > 0) ENFORCED', $sql);
    }

    public function testCheckNotEnforced(): void
    {
        $check = new Check('price > 0', 'chk_price', false);

        $ct = new CreateTable('products');
        $ct->addColumn(new Column('price'));
        $ct->addConstraint($check);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('CHECK (price > 0) NOT ENFORCED', $sql);
    }

    public function testIsEnforcedGetter(): void
    {
        $check1 = new Check('x > 0', 'chk', true);
        self::assertTrue($check1->isEnforced());

        $check2 = new Check('x > 0', 'chk', false);
        self::assertFalse($check2->isEnforced());

        $check3 = new Check('x > 0', 'chk');
        self::assertNull($check3->isEnforced());
    }
}
