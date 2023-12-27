<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Csv;

use Generator;
use RuntimeException;

/**
 * This class allows you to read and write csv easily if you
 * don't have League or OpenSpout installed
 */
class Native extends CsvAdapter
{
    public const BOM = "\xef\xbb\xbf";

    public function readString(
        string $contents,
        ...$opts
    ): Generator {
        $this->configure(...$opts);
        // check for bom
        if (strncmp($contents, self::BOM, 3) === 0) {
            $contents = substr($contents, 3);
        }

        // parse rows and take into account enclosure and escaped parts
        $data = str_getcsv($contents, $this->eol, $this->enclosure, $this->escape);

        $headers = null;
        foreach ($data as $line) {
            if ($this->assoc) {
                if ($headers === null) {
                    $headers = $line;
                    continue;
                }
                $line = array_combine($headers, $line);
            }
            yield str_getcsv($line, $this->separator, $this->enclosure, $this->escape);
        }
    }

    public function readFile(
        string $filename,
        ...$opts
    ): Generator {
        $this->configure(...$opts);
        $stream = fopen($filename, 'r');
        if (fgets($stream, 4) !== self::BOM) {
            // bom not found - rewind pointer to start of file.
            rewind($stream);
        }
        $headers = null;
        while (!feof($stream) && ($line = fgetcsv($stream, null, $this->separator, $this->enclosure, $this->escape)) !== false) {
            if ($this->assoc) {
                if ($headers === null) {
                    $headers = $line;
                    continue;
                }
                $line = array_combine($headers, $line);
            }
            yield $line;
        }
    }

    protected function write($stream, iterable $data)
    {
        if ($this->bom) {
            fputs($stream, self::BOM);
        }
        foreach ($data as $row) {
            $result = fputcsv($stream, $row, $this->separator, $this->enclosure, $this->escape, $this->eol);
            if ($result === false) {
                throw new RuntimeException("Failed to write line");
            }
        }
    }

    public function writeString(
        iterable $data,
        ...$opts
    ): string {
        $this->configure(...$opts);
        $stream = fopen('php://temp/maxmemory:' . (5 * 1024 * 1024), 'r+');
        $this->write($stream, $data);
        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);
        return $contents;
    }

    public function writeFile(
        iterable $data,
        string $filename,
        ...$opts
    ): bool {
        $this->configure(...$opts);
        $stream = fopen($filename, 'w');
        $this->write($stream, $data);
        fclose($stream);
        return true;
    }

    public function output(
        iterable $data,
        string $filename,
        ...$opts
    ): void {
        $this->configure(...$opts);

        header('Content-Type: text/csv');
        header(
            'Content-Disposition: attachment; ' .
                'filename="' . rawurlencode($filename) . '"; ' .
                'filename*=UTF-8\'\'' . rawurlencode($filename)
        );
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $stream = fopen('php://output', 'w');
        $this->write($stream, $data);
        fclose($stream);
    }
}
