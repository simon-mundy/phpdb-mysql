<?php

declare(strict_types=1);

namespace PhpDbTest\Mysql\Sql\Ddl\Index;

use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Mysql\AdapterPlatform;
use PhpDb\Mysql\Sql\Ddl\CreateTableDecorator;
use PhpDb\Mysql\Sql\Ddl\Index\FulltextIndex;
use PhpDb\Sql\Ddl\CreateTable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FulltextIndex::class)]
final class FulltextIndexTest extends TestCase
{
    private AdapterPlatform $platform;

    protected function setUp(): void
    {
        $driver         = $this->createMock(DriverInterface::class);
        $this->platform = new AdapterPlatform($driver);
    }

    public function testBasicFulltextIndex(): void
    {
        $ct = new CreateTable('articles');
        $ct->addConstraint(new FulltextIndex('content', 'idx_content'));

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('FULLTEXT INDEX', $sql);
        self::assertStringContainsString('`idx_content`', $sql);
        self::assertStringContainsString('`content`', $sql);
    }

    public function testFulltextIndexWithParser(): void
    {
        $index = new FulltextIndex('content', 'idx_content');
        $index->setParser('ngram');

        $ct = new CreateTable('articles');
        $ct->addConstraint($index);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('FULLTEXT INDEX', $sql);
        self::assertStringContainsString('WITH PARSER ngram', $sql);
    }

    public function testFulltextIndexMultipleColumns(): void
    {
        $index = new FulltextIndex(['title', 'content'], 'idx_search');

        $ct = new CreateTable('articles');
        $ct->addConstraint($index);

        $decorator = new CreateTableDecorator();
        $decorator->setSubject($ct);
        $sql = $decorator->getSqlString($this->platform);

        self::assertStringContainsString('FULLTEXT INDEX', $sql);
        self::assertStringContainsString('`idx_search`', $sql);
        self::assertStringContainsString('`title`', $sql);
        self::assertStringContainsString('`content`', $sql);
    }

    public function testGetParser(): void
    {
        $index = new FulltextIndex('content', 'idx_content');

        self::assertNull($index->getParser());

        $index->setParser('ngram');

        self::assertSame('ngram', $index->getParser());
    }
}
