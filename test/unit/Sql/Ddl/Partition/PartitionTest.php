<?php

declare(strict_types=1);

namespace PhpDbTest\Mysql\Sql\Ddl\Partition;

use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Mysql\AdapterPlatform;
use PhpDb\Mysql\Sql\Ddl\CreateTableDecorator;
use PhpDb\Mysql\Sql\Ddl\MysqlCreateTable;
use PhpDb\Mysql\Sql\Ddl\Partition\Partition;
use PhpDb\Mysql\Sql\Ddl\Partition\PartitionDefinition;
use PhpDb\Sql\Ddl\Column\Column;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Partition::class)]
#[CoversClass(PartitionDefinition::class)]
#[CoversClass(MysqlCreateTable::class)]
final class PartitionTest extends TestCase
{
    private AdapterPlatform $platform;

    protected function setUp(): void
    {
        $driver         = $this->createMock(DriverInterface::class);
        $this->platform = new AdapterPlatform($driver);
    }

    public function testHashPartitionWithCount(): void
    {
        $partition = new Partition('HASH', 'id', 4);

        $ct = new MysqlCreateTable('orders');
        $ct->addColumn(new Column('id'));
        $ct->setPartition($partition);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('PARTITION BY HASH(id) PARTITIONS 4', $sql);
    }

    public function testRangePartitionWithDefinitions(): void
    {
        $defs = [
            new PartitionDefinition('p0', 'LESS THAN', '2000'),
            new PartitionDefinition('p1', 'LESS THAN', '2010'),
            new PartitionDefinition('p2', 'LESS THAN', 'MAXVALUE'),
        ];

        $partition = new Partition('RANGE', 'YEAR(created)', null, $defs);

        $ct = new MysqlCreateTable('events');
        $ct->addColumn(new Column('created'));
        $ct->setPartition($partition);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('PARTITION BY RANGE(YEAR(created))', $sql);
        self::assertStringContainsString('PARTITION `p0` VALUES LESS THAN (2000)', $sql);
        self::assertStringContainsString('PARTITION `p1` VALUES LESS THAN (2010)', $sql);
        self::assertStringContainsString('PARTITION `p2` VALUES LESS THAN (MAXVALUE)', $sql);
    }

    public function testListPartition(): void
    {
        $defs = [
            new PartitionDefinition('p_east', 'IN', "'NY','NJ','CT'"),
            new PartitionDefinition('p_west', 'IN', "'CA','OR','WA'"),
        ];

        $partition = new Partition('LIST', 'state', null, $defs);

        $ct = new MysqlCreateTable('customers');
        $ct->addColumn(new Column('state'));
        $ct->setPartition($partition);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('PARTITION BY LIST(state)', $sql);
        self::assertStringContainsString("VALUES IN ('NY','NJ','CT')", $sql);
    }

    public function testPartitionDefinitionWithEngine(): void
    {
        $def = new PartitionDefinition('p0', 'LESS THAN', '100', 'InnoDB');

        $sql = $def->getSql($this->platform);

        self::assertStringContainsString('ENGINE = InnoDB', $sql);
    }

    public function testPartitionDefinitionWithComment(): void
    {
        $def = new PartitionDefinition('p0', 'LESS THAN', '100', null, 'First partition');

        $sql = $def->getSql($this->platform);

        self::assertStringContainsString('COMMENT = ', $sql);
        self::assertStringContainsString('First partition', $sql);
    }

    public function testPartitionGetters(): void
    {
        $partition = new Partition('HASH', 'id', 4);

        self::assertSame('HASH', $partition->getType());
        self::assertSame('id', $partition->getExpression());
        self::assertSame(4, $partition->getCount());
        self::assertSame([], $partition->getDefinitions());
    }

    public function testPartitionDefinitionGetters(): void
    {
        $def = new PartitionDefinition('p0', 'LESS THAN', '100', 'InnoDB', 'test');

        self::assertSame('p0', $def->getName());
        self::assertSame('LESS THAN', $def->getOperator());
        self::assertSame('100', $def->getValue());
        self::assertSame('InnoDB', $def->getEngine());
        self::assertSame('test', $def->getComment());
    }

    public function testKeyPartitionWithCount(): void
    {
        $partition = new Partition('KEY', 'id', 8);

        $ct = new MysqlCreateTable('sessions');
        $ct->addColumn(new Column('id'));
        $ct->setPartition($partition);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('PARTITION BY KEY(id) PARTITIONS 8', $sql);
    }

    public function testMysqlCreateTableGetPartition(): void
    {
        $partition = new Partition('HASH', 'id', 4);

        $ct = new MysqlCreateTable('test');
        self::assertNull($ct->getPartition());

        $ct->setPartition($partition);
        self::assertSame($partition, $ct->getPartition());
    }
}
