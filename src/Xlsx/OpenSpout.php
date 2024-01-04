<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Xlsx;

use Generator;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\AutoFilter;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;
use LeKoala\SpreadCompat\SpreadCompat;
use OpenSpout\Writer\XLSX\Entity\SheetView;

class OpenSpout extends XlsxAdapter
{
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

        $writer = new Writer();
        return $writer;
    }

    /**
     * Call this after opening
     *
     * @param Writer $writer
     * @return void
     */
    protected function setSheetView(Writer $writer)
    {
        if ($this->freezePane) {
            $sheetView = new SheetView();
            $row = (int)substr($this->freezePane, 1, 1);
            if ($row > 0) {
                $sheetView->setFreezeRow($row);
                $sheetView->setFreezeColumn(substr($this->freezePane, 0, 1));
            }
            $writer->getCurrentSheet()->setSheetView($sheetView);
        }
        if ($this->autofilter) {
            $c = $this->autofilterCoords();
            $autoFilter = new AutoFilter($c[0], $c[1], $c[2], $c[3]);
            $writer->getCurrentSheet()->setAutoFilter($autoFilter);
        }
    }

    public function autofilterCoords(): array
    {
        $parts = explode(":", $this->autofilter ?? "");
        $from = $parts[0];
        $to = $parts[1];

        $letters = range('A', 'Z');

        $fromColumnIndex = array_search(substr($from, 0, 1), $letters, true);
        $fromRow = (int)substr($from, 1, 1);
        $toColumnIndex = array_search(substr($to, 0, 1), $letters, true);
        $toRow = (int)substr($to, 1, 1);

        return [
            $fromColumnIndex,
            $fromRow,
            $toColumnIndex,
            $toRow,
        ];
    }

    public function writeFile(iterable $data, string $filename, ...$opts): bool
    {
        $this->configure(...$opts);
        $writer = $this->getWriter();

        //TODO: encoding?

        $writer->openToFile($filename);
        $this->setSheetView($writer);
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
        $this->setSheetView($writer);
        foreach ($data as $row) {
            $writer->addRow(Row::fromValues($row));
        }
        $writer->close();
    }
}
