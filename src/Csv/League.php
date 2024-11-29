<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Csv;

use Generator;
use League\Csv\InvalidArgument;
use League\Csv\Reader;
use League\Csv\Writer;
use League\Csv\CharsetConverter;
use RuntimeException;
use SplTempFileObject;

class League extends CsvAdapter
{
    public function readString(string $contents, ...$opts): Generator
    {
        $this->configure(...$opts);
        $this->configureSeparator($contents);

        return $this->read(Reader::createFromString($contents));
    }

    /**
     * @param resource $stream
     */
    public function readStream($stream, ...$opts): Generator
    {
        $this->configure(...$opts);
        $this->configureSeparator($stream);

        return $this->read(Reader::createFromStream($stream));
    }

    public function readFile(string $filename, ...$opts): Generator
    {
        $this->configure(...$opts);
        $this->configureSeparator($filename);

        return $this->read(Reader::createFromPath($filename));
    }

    /**
     * @param iterable<array<float|int|string|\Stringable|null>> $data
     * @param mixed ...$opts
     * @return string
     */
    public function writeString(iterable $data, ...$opts): string
    {
        $this->configure(...$opts);
        $csv = Writer::createFromString();
        $this->write($data, $csv);

        return $csv->toString();
    }

    /**
     * @param iterable<array<float|int|string|\Stringable|null>> $data
     * @param string $filename
     * @param mixed ...$opts
     * @return bool
     */
    public function writeFile(iterable $data, string $filename, ...$opts): bool
    {
        try {
            $this->configure(...$opts);
            $this->write($data, Writer::createFromPath($filename, 'w'));

            return true;
        } catch (RuntimeException) {
            return false;
        }
    }

    /**
     * @param iterable<array<float|int|string|\Stringable|null>> $data
     * @param string $filename
     * @param mixed ...$opts
     * @return void
     */
    public function output(iterable $data, string $filename, ...$opts): void
    {
        $this->configure(...$opts);
        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $this->write($data, $csv);
        $csv->output($filename);
    }

    protected function read(Reader $csv): Generator
    {
        $this->initialize($csv);

        if ($this->assoc) {
            $csv->setHeaderOffset(0);
        }

        foreach ($csv as $record) {
            yield $record;
        }
    }

    /**
     * @param iterable<array<float|int|string|\Stringable|null>> $data
     * @param Writer $csv
     * @return void
     */
    protected function write(iterable $data, Writer $csv): void
    {
        $this->initialize($csv);

        $csv->setEndOfLine($this->eol);
        if ($this->bom) {
            $csv->setOutputBOM(match ($this->getInputEncoding()) {
                'UTF-16BE' => Writer::BOM_UTF16_BE,
                'UTF-16LE' => Writer::BOM_UTF16_LE,
                'UTF-32BE' => Writer::BOM_UTF32_BE,
                'UTF-32LE' => Writer::BOM_UTF32_LE,
                default => Writer::BOM_UTF8
            });
        }

        if (!empty($this->headers)) {
            $csv->insertOne($this->headers);
        }

        $csv->insertAll($data);
    }

    /**
     * @throws InvalidArgument
     */
    protected function initialize(Reader|Writer $csv): void
    {
        $csv->setDelimiter($this->getSeparator());
        $csv->setEnclosure($this->enclosure);
        $csv->setEscape($this->escape);
        $defaultEncoding = mb_internal_encoding();
        $inputEncoding = $this->getInputEncoding() ?? $defaultEncoding;
        $outputEncoding = $this->getOutputEncoding() ?? $defaultEncoding;
        if ($inputEncoding === $outputEncoding) {
            return;
        }

        if ($csv->supportsStreamFilterOnWrite() || $csv->supportsStreamFilterOnRead()) {
            CharsetConverter::addTo($csv, $inputEncoding, $outputEncoding);
            return;
        }

        $csv->addFormatter(
            (new CharsetConverter())
                ->inputEncoding($inputEncoding)
                ->outputEncoding($outputEncoding)
        );
    }
}
