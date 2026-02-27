<?php

declare(strict_types=1);

namespace PhpDbTest\Mysql\Sql\Ddl\Index;

use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Mysql\AdapterPlatform;
use PhpDb\Mysql\Sql\Ddl\CreateTableDecorator;
use PhpDb\Mysql\Sql\Ddl\Index\FulltextIndex;
use PhpDb\Mysql\Sql\Ddl\Index\Index;
use PhpDb\Mysql\Sql\Ddl\Index\SpatialIndex;
use PhpDb\Sql\Ddl\Column\Column;
use PhpDb\Sql\Ddl\CreateTable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Index::class)]
#[CoversClass(FulltextIndex::class)]
#[CoversClass(SpatialIndex::class)]
final class IndexOptionsTest extends TestCase
{
    private AdapterPlatform $platform;

    protected function setUp(): void
    {
        $driver         = $this->createMock(DriverInterface::class);
        $this->platform = new AdapterPlatform($driver);
    }

    public function testIndexWithComment(): void
    {
        $idx = new Index('name', 'idx_name');
        $idx->setComment('Speeds up name lookups');

        $ct = new CreateTable('users');
        $ct->addColumn(new Column('name'));
        $ct->addConstraint($idx);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('INDEX', $sql);
        self::assertStringContainsString('COMMENT', $sql);
        self::assertStringContainsString('Speeds up name lookups', $sql);
    }

    public function testIndexVisible(): void
    {
        $idx = new Index('name', 'idx_name');
        $idx->setVisible(true);

        $ct = new CreateTable('users');
        $ct->addColumn(new Column('name'));
        $ct->addConstraint($idx);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('VISIBLE', $sql);
    }

    public function testIndexInvisible(): void
    {
        $idx = new Index('name', 'idx_name');
        $idx->setVisible(false);

        $ct = new CreateTable('users');
        $ct->addColumn(new Column('name'));
        $ct->addConstraint($idx);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('INVISIBLE', $sql);
    }

    public function testIndexWithDirection(): void
    {
        $idx = new Index(['name', 'age'], 'idx_name_age');
        $idx->setDirection(0, 'ASC');
        $idx->setDirection(1, 'DESC');

        $ct = new CreateTable('users');
        $ct->addColumn(new Column('name'));
        $ct->addColumn(new Column('age'));
        $ct->addConstraint($idx);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('ASC', $sql);
        self::assertStringContainsString('DESC', $sql);
    }

    public function testIndexWithType(): void
    {
        $idx = new Index('name', 'idx_name');
        $idx->setType('BTREE');

        $ct = new CreateTable('users');
        $ct->addColumn(new Column('name'));
        $ct->addConstraint($idx);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('USING BTREE', $sql);
    }

    public function testFulltextIndexWithComment(): void
    {
        $idx = new FulltextIndex('body', 'ft_body');
        $idx->setComment('Full-text search');

        $ct = new CreateTable('posts');
        $ct->addColumn(new Column('body'));
        $ct->addConstraint($idx);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('FULLTEXT INDEX', $sql);
        self::assertStringContainsString('COMMENT', $sql);
    }

    public function testFulltextIndexWithParserAndOptions(): void
    {
        $idx = new FulltextIndex('body', 'ft_body');
        $idx->setParser('ngram');
        $idx->setVisible(false);

        $ct = new CreateTable('posts');
        $ct->addColumn(new Column('body'));
        $ct->addConstraint($idx);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('WITH PARSER ngram', $sql);
        self::assertStringContainsString('INVISIBLE', $sql);
    }

    public function testSpatialIndexWithComment(): void
    {
        $idx = new SpatialIndex('location', 'sp_loc');
        $idx->setComment('Spatial lookup');

        $ct = new CreateTable('places');
        $ct->addColumn(new Column('location'));
        $ct->addConstraint($idx);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('SPATIAL INDEX', $sql);
        self::assertStringContainsString('COMMENT', $sql);
    }

    public function testSpatialIndexInvisible(): void
    {
        $idx = new SpatialIndex('location', 'sp_loc');
        $idx->setVisible(false);

        $ct = new CreateTable('places');
        $ct->addColumn(new Column('location'));
        $ct->addConstraint($idx);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('SPATIAL INDEX', $sql);
        self::assertStringContainsString('INVISIBLE', $sql);
    }

    public function testIndexGetters(): void
    {
        $idx = new Index('name', 'idx_name');
        $idx->setComment('test comment');
        $idx->setVisible(true);

        self::assertSame('test comment', $idx->getComment());
        self::assertTrue($idx->getVisible());
    }

    public function testFulltextIndexWithDirection(): void
    {
        $idx = new FulltextIndex(['title', 'body'], 'ft_content');
        $idx->setDirection(0, 'ASC');

        $ct = new CreateTable('posts');
        $ct->addColumn(new Column('title'));
        $ct->addColumn(new Column('body'));
        $ct->addConstraint($idx);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('FULLTEXT INDEX', $sql);
        self::assertStringContainsString('ASC', $sql);
    }
}
