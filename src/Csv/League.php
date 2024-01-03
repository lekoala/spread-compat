<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Csv;

use Generator;
use League\Csv\Reader;
use League\Csv\Writer;
use League\Csv\CharsetConverter;
use RuntimeException;

class League extends CsvAdapter
{
    protected function read(Reader $csv): Generator
    {
        if ($this->separator) {
            $csv->setDelimiter($this->separator);
        }
        if ($this->enclosure) {
            $csv->setEnclosure($this->enclosure);
        }
        if ($this->escape) {
            $csv->setEscape($this->escape);
        }
        if ($this->bom) {
            $csv->skipInputBOM();
        } else {
            $csv->includeInputBOM();
        }
        if ($this->inputEncoding) {
            $encoder = (new \League\Csv\CharsetConverter())
                ->inputEncoding($this->getInputEncoding() ?? mb_internal_encoding())
                ->outputEncoding($this->getOutputEncoding() ?? mb_internal_encoding());
            $csv->addFormatter($encoder);
        }
        if ($this->assoc) {
            $csv->setHeaderOffset(0);
        }
        $records = $csv->getRecords();
        foreach ($records as $record) {
            yield $record;
        }
    }

    public function readString(
        string $contents,
        ...$opts
    ): Generator {
        $this->configure(...$opts);
        $csv = Reader::createFromString($contents);
        yield from $this->read($csv);
    }

    public function readStream($stream, ...$opts): Generator
    {
        $this->configure(...$opts);
        $csv = Reader::createFromStream($stream);
        yield from $this->read($csv);
    }

    public function readFile(
        string $filename,
        ...$opts
    ): Generator {
        $this->configure(...$opts);
        $stream = fopen($filename, 'r');
        if (!$stream) {
            throw new RuntimeException("Failed to open $filename");
        }
        $csv = Reader::createFromStream($stream);
        yield from $this->read($csv);
    }

    protected function getWriter(iterable $data): Writer
    {
        $csv = Writer::createFromString();
        if ($this->separator) {
            $csv->setDelimiter($this->separator);
        }
        if ($this->enclosure) {
            $csv->setEnclosure($this->enclosure);
        }
        if ($this->escape) {
            $csv->setEscape($this->escape);
        }
        if ($this->eol) {
            $csv->setEndOfLine($this->eol);
        }
        if ($this->bom) {
            $csv->setOutputBOM($this->getBomString());
        }
        if ($this->outputEncoding) {
            $encoder = (new CharsetConverter())
                ->inputEncoding($this->getInputEncoding() ?? mb_internal_encoding())
                ->outputEncoding($this->getOutputEncoding() ?? mb_internal_encoding());
            $csv->addFormatter($encoder);
        }
        if (!empty($this->headers)) {
            $csv->insertOne($this->headers);
        }
        $csv->insertAll($data);
        return $csv;
    }

    public function writeString(iterable $data, ...$opts): string
    {
        $this->configure(...$opts);
        return $this->getWriter($data, ...$opts)->toString();
    }

    public function writeFile(iterable $data, string $filename, ...$opts): bool
    {
        $this->configure(...$opts);
        return file_put_contents($filename, $this->writeString($data, ...$opts)) > 0 ? true : false;
    }

    public function output(iterable $data, string $filename, ...$opts): void
    {
        $this->configure(...$opts);
        $this->getWriter($data, ...$opts)->output($filename);
        // ignore returned bytes
    }

    protected function getBomString(): string
    {
        return match ($this->getInputEncoding()) {
            'UTF-16BE' => Writer::BOM_UTF16_BE,
            'UTF-16LE' => Writer::BOM_UTF16_LE,
            'UTF-32BE' => Writer::BOM_UTF32_BE,
            'UTF-32LE' => Writer::BOM_UTF32_LE,
            'UTF-8' => Writer::BOM_UTF8,
            default => Writer::BOM_UTF8
        };
    }
}
