<?php

declare(strict_types=1);

namespace PhpDb\Mysql\Sql\Ddl;

use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Sql\Literal;

use function is_bool;
use function is_int;
use function str_replace;
use function strtolower;
use function strtoupper;

trait MysqlTableOptionsTrait
{
    /**
     * @return string[]
     */
    protected function buildMysqlTableOptions(PlatformInterface $platform): array
    {
        $parts = [];
        foreach ($this->options as $key => $value) {
            $parts[] = $this->formatMysqlTableOption($key, $value, $platform);
        }

        return $parts;
    }

    private function formatMysqlTableOption(
        string $key,
        Literal|bool|int|string $value,
        PlatformInterface $platform
    ): string {
        if ($value instanceof Literal) {
            $value = $value->getLiteral();
        }

        $normalized = strtolower(str_replace(['-', '_', ' '], '', $key));

        return match ($normalized) {
            'engine' => 'ENGINE = ' . $value,
            'charset', 'characterset' => 'DEFAULT CHARACTER SET = ' . $value,
            'collate', 'collation' => 'COLLATE = ' . $value,
            'comment' => 'COMMENT = ' . $platform->quoteTrustedValue((string) $value),
            'autoincrement' => 'AUTO_INCREMENT = ' . $value,
            'rowformat' => 'ROW_FORMAT = ' . strtoupper((string) $value),
            'keyblocksize' => 'KEY_BLOCK_SIZE = ' . $value,
            'compression' => 'COMPRESSION = ' . $platform->quoteTrustedValue(strtoupper((string) $value)),
            'encryption' => 'ENCRYPTION = ' . $platform->quoteTrustedValue(strtoupper((string) $value)),
            default => $this->formatDefaultTableOption($key, $value, $platform),
        };
    }

    private function formatDefaultTableOption(
        string $key,
        bool|int|string $value,
        PlatformInterface $platform
    ): string {
        $key = strtoupper($key);
        if (is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif (is_int($value)) {
            $value = (string) $value;
        } else {
            $value = $platform->quoteTrustedValue($value);
        }

        return $key . ' = ' . $value;
    }
}
