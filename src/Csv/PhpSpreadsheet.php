<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Csv;

use Exception;
use Generator;
use LeKoala\SpreadCompat\SpreadCompat;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv as ReaderCsv;
use PhpOffice\PhpSpreadsheet\Writer\Csv as WriterCsv;

/**
 * This is very slow and should probably not be used
 */
class PhpSpreadsheet extends CsvAdapter
{
    protected function getReader(): ReaderCsv
    {
        $reader = new ReaderCsv();
        if ($this->inputEncoding) {
            $reader->setInputEncoding($this->getInputEncoding() ?? mb_internal_encoding());
        }
        $reader->setDelimiter($this->getSeparator());
        $reader->setEnclosure($this->enclosure);
        $reader->setEscapeCharacter($this->escape);
        $reader->setSheetIndex(0);
        // $reader->setPreserveNullString(true);
        // We are only interested in cell data
        $reader->setReadDataOnly(true);
        return $reader;
    }

    protected function readSpreadsheet(Spreadsheet $spreadsheet): Generator
    {
        $worksheet = $spreadsheet->getActiveSheet();

        $headers = null;
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $data = [];
            foreach ($cellIterator as $cell) {
                $data[] = $cell->getValue();
            }
            if (empty($data) || $data[0] === null) {
                continue;
            }
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

    public function readString(
        string $contents,
        ...$opts
    ): Generator {
        $this->configure(...$opts);
        $spreadsheet = $this->getReader()->loadSpreadsheetFromString($contents);
        yield from $this->readSpreadsheet($spreadsheet);
    }

    /**
     * @param resource $stream
     */
    public function readStream($stream, ...$opts): Generator
    {
        throw new Exception("PhpSpreadsheet doesn't support streams");
    }

    public function readFile(
        string $filename,
        ...$opts
    ): Generator {
        $this->configure(...$opts);
        $spreadsheet = $this->getReader()->load($filename);
        yield from $this->readSpreadsheet($spreadsheet);
    }

    protected function getWriter(iterable $source): WriterCsv
    {
        $spreadsheet = new Spreadsheet();
        if (!is_array($source)) {
            $source = iterator_to_array($source);
        }
        $spreadsheet->getActiveSheet()->fromArray($source);
        $writer = new WriterCsv($spreadsheet);
        if ($this->outputEncoding) {
            $writer->setOutputEncoding($this->getOutputEncoding() ?? mb_internal_encoding());
        }
        $writer->setDelimiter($this->getSeparator());
        $writer->setEnclosure($this->enclosure);
        $writer->setLineEnding($this->eol);
        $writer->setSheetIndex(0);
        $writer->setUseBOM($this->bom);
        $writer->setEnclosureRequired(false); // Like the default php implementation
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
        $writer = $this->getWriter($data);
        $writer->save($filename);
        return true;
    }

    public function output(iterable $data, string $filename, ...$opts): void
    {
        $this->configure(...$opts);
        $writer = $this->getWriter($data);

        SpreadCompat::outputHeaders('text/csv', $filename);
        ob_end_clean();
        ob_start();
        $writer->save('php://output');
    }
}
