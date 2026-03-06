<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Csv;

use Generator;
use LeKoala\Baresheet\CsvReader;
use LeKoala\Baresheet\CsvWriter;

class Baresheet extends CsvAdapter
{
    public function readFile(string $filename, ...$opts): Generator
    {
        $this->configure(...$opts);
        $reader = new CsvReader();
        $reader->separator = $this->separator;
        $reader->enclosure = $this->enclosure;
        $reader->escape = $this->escape;
        // $reader->eol = $this->eol; // not supported by Baresheet
        $reader->inputEncoding = $this->inputEncoding;
        $reader->assoc = $this->assoc;
        return $reader->readFile($filename);
    }

    public function readStream($stream, ...$opts): Generator
    {
        $this->configure(...$opts);
        $reader = new CsvReader();
        $reader->separator = $this->separator;
        $reader->enclosure = $this->enclosure;
        $reader->escape = $this->escape;
        $reader->inputEncoding = $this->inputEncoding;
        $reader->assoc = $this->assoc;
        return $reader->readStream($stream);
    }

    public function writeFile(iterable $data, string $filename, ...$opts): bool
    {
        $this->configure(...$opts);
        $writer = new CsvWriter();
        $writer->separator = $this->separator;
        $writer->enclosure = $this->enclosure;
        $writer->escape = $this->escape;
        $writer->eol = $this->eol;
        $writer->bom = $this->bom;
        $writer->outputEncoding = $this->outputEncoding;
        $writer->headers = $this->headers;
        $writer->escapeFormulas = $this->escapeFormulas;
        /** @var iterable<array<float|int|string|\Stringable|null>> $data */
        return $writer->writeFile($data, $filename);
    }

    public function output(iterable $data, string $filename, ...$opts): void
    {
        $this->configure(...$opts);
        $writer = new CsvWriter();
        $writer->separator = $this->separator;
        $writer->enclosure = $this->enclosure;
        $writer->escape = $this->escape;
        $writer->eol = $this->eol;
        $writer->bom = $this->bom;
        $writer->outputEncoding = $this->outputEncoding;
        $writer->headers = $this->headers;
        $writer->escapeFormulas = $this->escapeFormulas;
        /** @var iterable<array<float|int|string|\Stringable|null>> $data */
        $writer->output($data, $filename);
    }

    public function writeString(iterable $data, ...$opts): string
    {
        $this->configure(...$opts);
        $writer = new CsvWriter();
        $writer->separator = $this->separator;
        $writer->enclosure = $this->enclosure;
        $writer->escape = $this->escape;
        $writer->eol = $this->eol;
        $writer->bom = $this->bom;
        $writer->outputEncoding = $this->outputEncoding;
        $writer->headers = $this->headers;
        $writer->escapeFormulas = $this->escapeFormulas;
        /** @var iterable<array<float|int|string|\Stringable|null>> $data */
        return $writer->writeString($data);
    }
}
