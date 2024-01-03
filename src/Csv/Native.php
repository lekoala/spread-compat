<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Csv;

use Generator;
use RuntimeException;

/**
 * This class allows you to read and write csv easily if you
 * don't have League or OpenSpout installed
 *
 * It's also the fastest adapter as far as I can tell
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
        /** @var array<string> $data */
        $data = str_getcsv($contents, $this->eol, $this->enclosure, $this->escape);

        $headers = null;
        foreach ($data as $line) {
            $row = str_getcsv($line, $this->separator, $this->enclosure, $this->escape);
            if ($this->assoc) {
                if ($headers === null) {
                    $headers = $row;
                    continue;
                }
                $row = array_combine($headers, $row);
            }
            yield $row;
        }
    }

    /**
     * @param resource $stream
     */
    public function readStream($stream, ...$opts): Generator
    {
        $this->configure(...$opts);
        if (fgets($stream, 4) !== self::BOM) {
            // bom not found - rewind pointer to start of file.
            rewind($stream);
        }
        $headers = null;
        while (
            !feof($stream)
            &&
            ($line = fgetcsv($stream, null, $this->separator, $this->enclosure, $this->escape)) !== false
        ) {
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

    public function readFile(
        string $filename,
        ...$opts
    ): Generator {
        $stream = fopen($filename, 'r');
        if (!$stream) {
            throw new RuntimeException("Failed to read stream");
        }
        yield from $this->readStream($stream, ...$opts);
    }

    /**
     * @param resource $stream
     * @param iterable $data
     * @return void
     */
    protected function write($stream, iterable $data): void
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
        if (!$stream) {
            throw new RuntimeException("Failed to read stream");
        }
        $this->write($stream, $data);
        rewind($stream);
        $contents = stream_get_contents($stream);
        if (!$contents) {
            $contents = "";
        }
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
        if (!$stream) {
            throw new RuntimeException("Failed to read stream");
        }
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
        if (!$stream) {
            throw new RuntimeException("Failed to read stream");
        }
        $this->write($stream, $data);
        fclose($stream);
    }
}
