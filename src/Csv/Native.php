<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Csv;

use Generator;
use LeKoala\SpreadCompat\SpreadCompat;
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
        $this->configureSeparator($contents);

        // check for bom
        if (strncmp($contents, self::BOM, 3) === 0) {
            $contents = substr($contents, 3);
        }
        $separator = $this->getSeparator();

        // parse rows and take into account enclosure and escaped parts
        // we use $this->eol as a separator as a mean to split lines
        /** @var array<string> $data */
        $data = str_getcsv($contents, $this->eol, $this->enclosure, $this->escape);

        $headers = null;
        foreach ($data as $line) {
            $row = str_getcsv($line, $separator, $this->enclosure, $this->escape);
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
        $this->configureSeparator($stream);

        if (fgets($stream, 4) !== self::BOM) {
            // bom not found - rewind pointer to start of file.
            rewind($stream);
        }
        $headers = null;
        $separator = $this->getSeparator();

        while (
            !feof($stream)
            &&
            ($line = fgetcsv($stream, null, $separator, $this->enclosure, $this->escape)) !== false
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
        $stream = SpreadCompat::getInputStream($filename);
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

        $separator = $this->getSeparator();
        foreach ($data as $row) {
            $result = fputcsv($stream, $row, $separator, $this->enclosure, $this->escape, $this->eol);
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

        $stream = SpreadCompat::getMaxMemTempStream();
        $this->write($stream, $data);
        $contents = SpreadCompat::getStreamContents($stream);
        fclose($stream);
        return $contents;
    }

    public function writeFile(
        iterable $data,
        string $filename,
        ...$opts
    ): bool {
        $this->configure(...$opts);
        $stream = SpreadCompat::getOutputStream($filename);
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

        SpreadCompat::outputHeaders('text/csv', $filename);
        $stream = SpreadCompat::getOutputStream();
        $this->write($stream, $data);
        fclose($stream);
    }
}
