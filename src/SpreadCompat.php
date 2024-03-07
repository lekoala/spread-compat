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

    public static function getAdapterName(string $ext): string
    {
        if (!$ext) {
            throw new Exception("The file has no extension and no extension parameter is set");
        }

        $ext = strtolower($ext);

        // Legacy xls is only supported by PhpSpreadsheet
        if ($ext === self::EXT_XLS) {
            if (class_exists(\PhpOffice\PhpSpreadsheet\Worksheet\Row::class)) {
                return self::PHP_SPREADSHEET;
            }
        }

        if ($ext === self::EXT_CSV) {
            if (self::$preferredCsvAdapter !== null) {
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
            if (self::$preferredXslxAdapter !== null) {
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
            return self::NATIVE;
        }

        throw new Exception("No adapter found for $ext");
    }

    public static function getAdapter(string $ext): SpreadInterface
    {
        $name = self::getAdapterName($ext);
        $ext = ucfirst($ext);
        $class = 'LeKoala\\SpreadCompat\\' . $ext . '\\' . $name;
        if (!class_exists($class)) {
            throw new Exception("Invalid adapter $class");
        }
        return new ($class);
    }

    public static function getAdapterByName(string $ext, string $name): SpreadInterface
    {
        $ext = ucfirst($ext);
        $class = 'LeKoala\\SpreadCompat\\' . $ext . '\\' . $name;
        if (!class_exists($class)) {
            throw new Exception("Invalid adapter $class");
        }
        return new ($class);
    }

    public static function getAdapterForFile(string $filename, string $ext = null): SpreadInterface
    {
        if ($ext === null) {
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
        }
        return self::getAdapter($ext);
    }

    /**
     * @return string
     */
    public static function getTempFilename(): string
    {
        $result = tempnam(sys_get_temp_dir(), 'S_C'); // windows only use the 3 first letters
        if ($result === false) {
            throw new Exception("Unable to create temp file");
        }
        return $result;
    }

    public static function isTempFile(string $file): bool
    {
        return str_starts_with(basename($file), 'S_C');
    }

    /**
     * Try to determine based on contents
     * Expect csv to be all printable chars
     */
    public static function getExtensionForContent(string $contents): string
    {
        if (ctype_print($contents)) {
            $ext = self::EXT_CSV;
        } else {
            $ext = self::EXT_XLSX;
        }
        return $ext;
    }

    /**
     * Don't forget fclose afterwards if you don't need the stream anymore
     *
     * @param resource $stream
     */
    public static function getStreamContents($stream): string
    {
        // Rewind to 0 before getting content from the start
        rewind($stream);
        $contents = stream_get_contents($stream);
        if ($contents === false) {
            $contents = "";
        }
        return $contents;
    }

    /**
     * The memory limit of php://temp can be controlled by appending /maxmemory:NN,
     * where NN is the maximum amount of data to keep in memory before using a temporary file, in bytes.
     *
     * @return resource
     */
    public static function getMaxMemTempStream()
    {
        $mb = 4;
        // Open for reading and writing; place the file pointer at the beginning of the file.
        $stream = fopen('php://temp/maxmemory:' . ($mb * 1024 * 1024), 'r+');
        if (!$stream) {
            throw new RuntimeException("Failed to open stream");
        }
        return $stream;
    }

    /**
     * @return resource
     */
    public static function getOutputStream(string $filename = 'php://output')
    {
        // Open for writing only; place the file pointer at the beginning of the file
        // and truncate the file to zero length. If the file does not exist, attempt to create it.
        $stream = fopen($filename, 'w');
        if (!$stream) {
            throw new RuntimeException("Failed to open stream");
        }
        return $stream;
    }

    /**
     * @return resource
     */
    public static function getInputStream(string $filename)
    {
        // Open for reading only; place the file pointer at the beginning of the file.
        $stream = fopen($filename, 'r');
        if (!$stream) {
            throw new RuntimeException("Failed to open stream");
        }
        return $stream;
    }

    public static function ensureExtension(string $filename, string $ext): string
    {
        $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
        if ($fileExt != $ext) {
            $filename .= ".$ext";
        }
        return $filename;
    }

    public static function outputHeaders(string $contentType, string $filename): void
    {
        if (headers_sent()) {
            throw new RuntimeException("Headers already sent");
        }

        header('Content-Type: ' . $contentType);
        header(
            'Content-Disposition: attachment; ' .
                'filename="' . rawurlencode($filename) . '"; ' .
                'filename*=UTF-8\'\'' . rawurlencode($filename)
        );
        header('Cache-Control: max-age=0');
        header('Pragma: public');
    }

    /**
     * @return Generator<string>
     */
    public static function excelColumnRange(string $lower = 'A', string $upper = 'ZZ'): Generator
    {
        ++$upper;
        for ($i = $lower; $i !== $upper; ++$i) {
            yield $i;
        }
    }

    /**
     * String from column index.
     *
     * @param int $index Column index (1 = A)
     */
    public static function getLetter($index): string
    {
        foreach (self::excelColumnRange() as $letter) {
            $index--;
            if ($index <= 0) {
                return $letter;
            }
        }
        return 'A';
    }

    public static function excelCell(int $row = 0, int $column = 0, bool $absolute = false): string
    {
        $n = $column;
        for ($r = ""; $n >= 0; $n = intval($n / 26) - 1) {
            $r = chr($n % 26 + 0x41) . $r;
        }
        if ($absolute) {
            return '$' . $r . '$' . ($row + 1);
        }
        return $r . ($row + 1);
    }

    protected static function getExtensionFromOpts(array $opts, ?string $fallback = null): ?string
    {
        return $opts[0]['extension'] ?? $opts['extension'] ?? $fallback;
    }

    public static function read(
        string $filename,
        ...$opts
    ): Generator {
        $ext = self::getExtensionFromOpts($opts);
        return static::getAdapterForFile($filename, $ext)->readFile($filename, ...$opts);
    }

    public static function readString(
        string $contents,
        string $ext = null,
        ...$opts
    ): Generator {
        $ext = self::getExtensionFromOpts($opts, $ext);
        if ($ext === null) {
            $ext = self::getExtensionForContent($contents);
        }
        return static::getAdapter($ext)->readString($contents, ...$opts);
    }

    public static function write(
        iterable $data,
        string $filename,
        ...$opts
    ): bool {
        $ext = self::getExtensionFromOpts($opts);
        return static::getAdapterForFile($filename, $ext)->writeFile($data, $filename, ...$opts);
    }

    public static function output(
        iterable $data,
        string $filename,
        ...$opts
    ): void {
        $ext = self::getExtensionFromOpts($opts);
        if ($ext) {
            $filename = self::ensureExtension($filename, $ext);
        }
        static::getAdapterForFile($filename, $ext)->output($data, $filename, ...$opts);
    }
}
