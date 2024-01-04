<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Common;

use Exception;
use Generator;
use LeKoala\SpreadCompat\SpreadCompat;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\BaseReader;
use PhpOffice\PhpSpreadsheet\Writer\BaseWriter;

trait PhpSpreadsheetUtils
{
    protected function getReaderClass(): string
    {
        throw new Exception("Method not implemented");
    }

    protected function getWriterClass(): string
    {
        throw new Exception("Method not implemented");
    }

    protected function getMimetype(): string
    {
        throw new Exception("Method not implemented");
    }

    protected function getReader(): BaseReader
    {
        $class = $this->getReaderClass();
        /** @var \PhpOffice\PhpSpreadsheet\Reader\Xls|\PhpOffice\PhpSpreadsheet\Reader\Xlsx $reader */
        $reader = new ($class);
        // We are only interested in cell data
        $reader->setReadDataOnly(true);
        return $reader;
    }

    protected function readSpreadsheet(Spreadsheet $spreadsheet): Generator
    {
        $headers = null;
        foreach ($spreadsheet->getActiveSheet()->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $data = [];
            foreach ($cellIterator as $cell) {
                $v = $cell->getValue();
                $data[] = $v;
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

    public function readFile(
        string $filename,
        ...$opts
    ): Generator {
        $this->configure(...$opts);
        $spreadsheet = $this->getReader()->load($filename);
        yield from $this->readSpreadsheet($spreadsheet);
    }

    protected function getWriter(iterable $source): BaseWriter
    {
        $spreadsheet = new Spreadsheet();
        if (!is_array($source)) {
            $source = iterator_to_array($source);
        }
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($source);
        if ($this->autofilter) {
            $sheet->setAutoFilter($this->autofilter);
        }
        if ($this->freezePane) {
            $sheet->freezePane($this->freezePane);
        }
        $class = $this->getWriterClass();
        /** @var \PhpOffice\PhpSpreadsheet\Writer\Xls|\PhpOffice\PhpSpreadsheet\Writer\Xlsx $writer */
        $writer = new ($class)($spreadsheet);
        return $writer;
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

        SpreadCompat::outputHeaders($this->getMimetype(), $filename);
        ob_end_clean();
        ob_start();
        $writer->save('php://output');
    }
}
