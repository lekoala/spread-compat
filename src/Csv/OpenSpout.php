<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Csv;

use Exception;
use Generator;
use LeKoala\SpreadCompat\SpreadCompat;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\CSV\Reader;
use OpenSpout\Writer\CSV\Writer;

class OpenSpout extends CsvAdapter
{
    public function readString(
        string $contents,
        ...$opts
    ): Generator {
        $filename = SpreadCompat::getTempFilename();
        file_put_contents($filename, $contents);
        yield from $this->readFile($filename);
        unlink($filename);
    }

    public function readFile(
        string $filename,
        ...$opts
    ): Generator {
        $this->configure(...$opts);
        $this->configureSeparator($filename);
        $options = new \OpenSpout\Reader\CSV\Options();

        $options->FIELD_DELIMITER = $this->getSeparator();
        $options->FIELD_ENCLOSURE = $this->enclosure;
        if ($this->inputEncoding) {
            $options->ENCODING = $this->getInputEncoding() ?? mb_internal_encoding();
        }
        $headers = null;
        //TODO: support escape

        $reader = new Reader($options);
        $reader->open($filename);
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $data = $row->toArray();
                if ($this->assoc) {
                    if ($headers === null) {
                        $headers = $data;
                        continue;
                    }
                    $data = array_combine($headers, $data);
                }
                yield $data;
            }
        }
        $reader->close();
    }

    /**
     * @param resource $stream
     */
    public function readStream($stream, ...$opts): Generator
    {
        //@link https://github.com/openspout/openspout/issues/71
        throw new Exception("OpenSpout doesn't support streams");
    }

    protected function getWriter(): Writer
    {
        $options = new \OpenSpout\Writer\CSV\Options();
        $options->FIELD_DELIMITER = $this->getSeparator();
        $options->FIELD_ENCLOSURE = $this->enclosure;
        $options->SHOULD_ADD_BOM = $this->bom;
        $writer = new Writer($options);
        return $writer;
    }

    public function writeString(iterable $data, ...$opts): string
    {
        $this->configure(...$opts);
        $filename = SpreadCompat::getTempFilename();
        $this->writeFile($data, $filename);
        $contents = file_get_contents($filename);
        if (!$contents) {
            $contents = "";
        }
        unlink($filename);
        return $contents;
    }

    public function writeFile(iterable $data, string $filename, ...$opts): bool
    {
        $this->configure(...$opts);
        $writer = $this->getWriter();

        //TODO: encoding?

        $writer->openToFile($filename);
        foreach ($data as $row) {
            $writer->addRow(Row::fromValues($row));
        }
        $writer->close();
        return true;
    }

    public function output(iterable $data, string $filename, ...$opts): void
    {
        $this->configure(...$opts);
        $writer = $this->getWriter();

        //TODO: encoding?

        $writer->openToBrowser($filename);
        foreach ($data as $row) {
            $writer->addRow(Row::fromValues($row));
        }
        $writer->close();
    }
}
