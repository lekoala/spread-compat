<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Xlsx;

use Generator;
use RuntimeException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;

class PhpSpreadsheet extends XlsxAdapter
{
    protected function getReader(): ReaderXlsx
    {
        $reader = new ReaderXlsx();
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

    public function readString(
        string $contents,
        ...$opts
    ): Generator {
        $file = tmpfile();
        if (!$file) {
            throw new RuntimeException("Could not get temp file");
        }
        $filename = stream_get_meta_data($file)['uri'];
        file_put_contents($filename, $contents);
        yield from $this->readFile($filename);
        unlink($filename);
    }

    public function readFile(
        string $filename,
        ...$opts
    ): Generator {
        $this->configure(...$opts);
        $spreadsheet = $this->getReader()->load($filename);
        yield from $this->readSpreadsheet($spreadsheet);
    }

    protected function getWriter(iterable $source): WriterXlsx
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
        $writer = new WriterXlsx($spreadsheet);
        return $writer;
    }

    public function writeString(iterable $data, ...$opts): string
    {
        $file = tmpfile();
        if (!$file) {
            throw new RuntimeException("Could not get temp file");
        }
        $filename = stream_get_meta_data($file)['uri'];
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

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header(
            'Content-Disposition: attachment; ' .
                'filename="' . rawurlencode($filename) . '"; ' .
                'filename*=UTF-8\'\'' . rawurlencode($filename)
        );
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        ob_end_clean();
        ob_start();
        $writer->save('php://output');
    }
}
