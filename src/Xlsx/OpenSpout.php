<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Xlsx;

use Generator;
use RuntimeException;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Writer\XLSX\Entity\SheetView;

class OpenSpout extends XlsxAdapter
{
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
        $options = new \OpenSpout\Reader\XLSX\Options();

        $headers = [];
        $reader = new Reader($options);
        $reader->open($filename);
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $data = $row->toArray();
                if ($this->assoc) {
                    if (empty($headers)) {
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

    protected function getWriter(): Writer
    {
        $options = new \OpenSpout\Writer\XLSX\Options();
        $writer = new Writer($options);
        if ($this->creator) {
            $writer->setCreator($this->creator);
        }

        $sheetView = new SheetView();
        if ($this->freezePane) {
            $row = (int)substr($this->freezePane, 1, 1);
            if ($row > 0) {
                $sheetView->setFreezeRow($row);
                $sheetView->setFreezeColumn(substr($this->freezePane, 0, 1));
            }
        }
        $writer = new Writer();
        $writer->getCurrentSheet()->setSheetView($sheetView);

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
