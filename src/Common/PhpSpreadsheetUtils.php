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
    /** @return array<string, string> */
    protected function getColumnFormats(): array
    {
        return [];
    }

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

    /**
     * @param Spreadsheet $spreadsheet
     * @return Generator<mixed>
     */
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
            $empty = !array_filter(
                $data,
                static fn ($value) => $value !== null && $value !== ''
            );
            if ($empty) {
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

    /**
     * @param string $filename
     * @param mixed ...$opts
     * @return Generator<mixed>
     */
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
        if ($this->getColumnFormats() !== []) {
            foreach ($this->getColumnFormats() as $column => $format) {
                $column = strtoupper($column);
                if (!preg_match('/^[A-Z]{1,3}$/', $column) || $format === '') {
                    throw new \InvalidArgumentException('Invalid Excel column format');
                }
                $sheet->getStyle($column . ':' . $column)->getNumberFormat()->setFormatCode($format);
                if ($format === '@') {
                    $index = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($column) - 1;
                    foreach ($source as $rowIndex => $row) {
                        if (!is_array($row)) {
                            continue;
                        }
                        $values = array_values($row);
                        if (
                            isset($values[$index])
                            && (is_scalar($values[$index]) || $values[$index] instanceof \Stringable)
                        ) {
                            $sheet->getCell($column . ($rowIndex + 1))->setValueExplicit(
                                (string) $values[$index],
                                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                            );
                        }
                    }
                }
            }
        }
        if ($this->autofilter) {
            $sheet->setAutoFilter($this->autofilter);
        }
        if ($this->freezePane) {
            $sheet->freezePane($this->freezePane);
        }

        $properties = $spreadsheet->getProperties();
        if ($this->creator !== null) {
            $properties->setCreator($this->creator);
            $properties->setLastModifiedBy($this->creator);
        }
        if ($this->title !== null) {
            $properties->setTitle($this->title);
        }
        if ($this->subject !== null) {
            $properties->setSubject($this->subject);
        }
        if ($this->description !== null) {
            $properties->setDescription($this->description);
        }
        if ($this->keywords !== null) {
            $properties->setKeywords($this->keywords);
        }
        if ($this->category !== null) {
            $properties->setCategory($this->category);
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
        if (ob_get_level() > 0) {
            ob_clean();
        }
        $writer->save('php://output');
    }
}
