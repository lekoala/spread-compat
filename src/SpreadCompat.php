<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat;

use Exception;
use Generator;
use RuntimeException;

/**
 * This class provides a static facade for adapters
 */
class SpreadCompat
{
    // Adapters names
    public const PHP_SPREADSHEET = "PhpSpreadsheet";
    public const OPEN_SPOUT = "OpenSpout";
    public const LEAGUE = "League";
    public const SIMPLE = "Simple";
    public const NATIVE = "Native";

    public const EXT_XLS = "xls";
    public const EXT_XLSX = "xlsx";
    public const EXT_CSV = "csv";

    public static ?string $preferredCsvAdapter = null;
    public static ?string $preferredXslxAdapter = null;

    public static function getAdapterName(string $filename, string $ext = null): string
    {
        if ($ext === null) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
        }
        $ext = strtolower($ext);

        // Legacy xls is only supported by PhpSpreadsheet
        if ($ext === self::EXT_XLS) {
            if (class_exists(\PhpOffice\PhpSpreadsheet\Worksheet\Row::class)) {
                return self::PHP_SPREADSHEET;
            }
            throw new Exception("No adapter found for xls");
        }

        if ($ext === self::EXT_CSV) {
            if (self::$preferredCsvAdapter) {
                return self::$preferredCsvAdapter;
            }
            if (class_exists(\League\Csv\Reader::class)) {
                return self::LEAGUE;
            }
            if (class_exists(\OpenSpout\Common\Entity\Row::class)) {
                return self::OPEN_SPOUT;
            }
            return self::NATIVE;

            // You probably don't want to use php spreadsheet for csv
        }

        if ($ext === self::EXT_XLSX) {
            if (self::$preferredXslxAdapter) {
                return self::$preferredXslxAdapter;
            }
            if (class_exists(\Shuchkin\SimpleXLSX::class)) {
                return self::SIMPLE;
            }
            if (class_exists(\OpenSpout\Common\Entity\Row::class)) {
                return self::OPEN_SPOUT;
            }
            if (class_exists(\PhpOffice\PhpSpreadsheet\Worksheet\Row::class)) {
                return self::PHP_SPREADSHEET;
            }
        }

        throw new Exception("No adapter found for $ext");
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

    public static function getTempFilename(): string
    {
        $file = tmpfile();
        if (!$file) {
            throw new RuntimeException("Could not get temp file");
        }
        $filename = stream_get_meta_data($file)['uri'];
        return $filename;
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
