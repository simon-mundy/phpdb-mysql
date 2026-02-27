<?php

declare(strict_types=1);

namespace PhpDbTest\Mysql\Sql\Ddl;

use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Mysql\AdapterPlatform;
use PhpDb\Mysql\Sql\Ddl\CreateIndex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreateIndex::class)]
final class CreateIndexTest extends TestCase
{
    private AdapterPlatform $platform;

    protected function setUp(): void
    {
        $driver         = $this->createMock(DriverInterface::class);
        $this->platform = new AdapterPlatform($driver);
    }

    public function testBasicIndex(): void
    {
        $idx = new CreateIndex('idx_name', 'users', ['name']);
        $sql = $idx->getSqlString($this->platform);

        self::assertStringContainsString('CREATE', $sql);
        self::assertStringContainsString('INDEX', $sql);
        self::assertStringContainsString('`idx_name`', $sql);
        self::assertStringContainsString('ON `users`', $sql);
        self::assertStringContainsString('`name`', $sql);
    }

    public function testUniqueIndex(): void
    {
        $idx = new CreateIndex('uq_email', 'users', ['email'], null, [], true);
        $sql = $idx->getSqlString($this->platform);

        self::assertStringContainsString('CREATE UNIQUE', $sql);
        self::assertStringContainsString('INDEX', $sql);
        self::assertStringContainsString('`uq_email`', $sql);
    }

    public function testFulltextIndex(): void
    {
        $idx = new CreateIndex('ft_body', 'posts', ['body'], 'FULLTEXT');
        $sql = $idx->getSqlString($this->platform);

        self::assertStringContainsString('FULLTEXT', $sql);
        self::assertStringContainsString('INDEX', $sql);
    }

    public function testSpatialIndex(): void
    {
        $idx = new CreateIndex('sp_loc', 'places', ['location'], 'SPATIAL');
        $sql = $idx->getSqlString($this->platform);

        self::assertStringContainsString('SPATIAL', $sql);
        self::assertStringContainsString('INDEX', $sql);
    }

    public function testMultipleColumns(): void
    {
        $idx = new CreateIndex('idx_name_age', 'users', ['name', 'age']);
        $sql = $idx->getSqlString($this->platform);

        self::assertStringContainsString('`name`', $sql);
        self::assertStringContainsString('`age`', $sql);
    }

    public function testColumnsWithLengths(): void
    {
        $idx = new CreateIndex('idx_name', 'users', ['name'], null, [10]);
        $sql = $idx->getSqlString($this->platform);

        self::assertStringContainsString('`name`(10)', $sql);
    }
}
