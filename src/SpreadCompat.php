<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat;

use Exception;
use Generator;

/**
 * This class provides a static facade for adapters
 */
class SpreadCompat
{
    public static function getAdapterName(string $filename, string $ext = null): string
    {
        if ($ext === null) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
        }
        $ext = strtolower($ext);
        if ($ext === "xls") {
            if (class_exists(\PhpOffice\PhpSpreadsheet\Worksheet\Row::class)) {
                return 'PhpSpreadsheet';
            }
            throw new Exception("No adapter found");
        }
        if (class_exists(\OpenSpout\Common\Entity\Row::class)) {
            return 'OpenSpout';
        }
        if ($ext === "csv" && class_exists(\LeKoala\SpreadCompat\Csv\League::class)) {
            return 'League';
        }
        if ($ext === "xlsx" && class_exists(\Shuchkin\SimpleXLSX::class)) {
            return 'Simple';
        }
        if (class_exists(\PhpOffice\PhpSpreadsheet\Worksheet\Row::class)) {
            return 'PhpSpreadsheet';
        }
        if ($ext === "csv") {
            return 'Native';
        }
        throw new Exception("No adapter found");
    }

    public static function getAdapter(string $filename, string $ext = null): SpreadInterface
    {
        if ($ext === null) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
        }
        $ext = ucfirst($ext);
        $name = self::getAdapterName($filename, $ext);
        $class = 'LeKoala\\SpreadCompat\\' . $ext . '\\' . $name;
        if (!class_exists($class)) {
            throw new Exception("Invalid adapter $class for $filename");
        }
        return new ($class);
    }

    public static function read(
        string $filename,
        ...$opts
    ): Generator {
        $ext = $opts['extension'] ?? null;
        if ($ext) {
            unset($opts['extension']);
        }
        return static::getAdapter($filename, $ext)->readFile($filename, ...$opts);
    }

    public static function write(
        iterable $data,
        string $filename,
        ...$opts
    ): bool {
        $ext = $opts['extension'] ?? null;
        if ($ext) {
            unset($opts['extension']);
        }
        return static::getAdapter($filename, $ext)->writeFile($data, $filename, ...$opts);
    }

    public static function output(
        iterable $data,
        string $filename,
        ...$opts
    ): void {
        $ext = $opts['extension'] ?? null;
        if ($ext) {
            unset($opts['extension']);
        }
        static::getAdapter($filename, $ext)->output($data, $filename, ...$opts);
    }
}
