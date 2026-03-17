<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Xlsx;

use Generator;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\AutoFilter;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Properties;

class OpenSpout extends XlsxAdapter
{
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
        $options = new \OpenSpout\Reader\XLSX\Options();

        $headers = [];
        $reader = new Reader($options);
        // If you have a validation issue saying "Validation failed: no DTD found !" maybe your php version is too old
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
        $optionsClass = new \ReflectionClass(\OpenSpout\Writer\XLSX\Options::class);

        // OpenSpout v5: readonly Options with a Properties value object.
        // ReflectionClass::isReadOnly only exists as of PHP 8.2, so guard the call.
        /** @phpstan-ignore-next-line method_exists always true in PHPStan's reflection stub */
        $isReadOnlyClass = method_exists($optionsClass, 'isReadOnly') && $optionsClass->isReadOnly();
        if ($isReadOnlyClass && class_exists(Properties::class)) {
            $creator = $this->creator ?? 'OpenSpout';
            $options = new \OpenSpout\Writer\XLSX\Options(
                properties: new Properties(
                    creator: $creator,
                    lastModifiedBy: $creator,
                ),
            );
            return new Writer($options);
        }

        // Older OpenSpout versions: use default options and, if supported,
        // configure the creator directly on the writer instance.
        $options = new \OpenSpout\Writer\XLSX\Options();
        $writer = new Writer($options);
        if ($this->creator && method_exists($writer, 'setCreator')) {
            $writer->setCreator($this->creator);
        }

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
                $column = substr($this->freezePane, 0, 1);
                if (method_exists($sheetView, 'setFreezeRow')) {
                    // OpenSpout v4 style API
                    $sheetView->setFreezeRow($row);
                    $sheetView->setFreezeColumn($column);
                } elseif (method_exists($sheetView, 'withFreezeRow')) {
                    // OpenSpout v5 immutable API
                    $sheetView = $sheetView
                        ->withFreezeRow($row)
                        ->withFreezeColumn($column);
                }
            }
            $writer->getCurrentSheet()->setSheetView($sheetView);
        }
        if ($this->autofilter) {
            $c = $this->autofilterCoords();
            $autoFilter = new AutoFilter($c[0], $c[1], $c[2], $c[3]);
            $writer->getCurrentSheet()->setAutoFilter($autoFilter);
        }
    }

    /**
     * @return array{int<0,25>,positive-int,int<0,25>,positive-int}
     */
    public function autofilterCoords(): array
    {
        $parts = explode(":", $this->autofilter ?? "");
        $from = $parts[0];
        $to = $parts[1];

        $fromColumnIndex = ord(substr($from, 0, 1)) - ord('A');
        $fromRow = (int)substr($from, 1, 1);
        $toColumnIndex = ord(substr($to, 0, 1)) - ord('A');
        $toRow = (int)substr($to, 1, 1);

        /** @var int<0, 25> $fromColumnIndex */
        /** @var positive-int $fromRow */
        /** @var int<0, 25> $toColumnIndex */
        /** @var positive-int $toRow */

        return [
            $fromColumnIndex,
            $fromRow,
            $toColumnIndex,
            $toRow,
        ];
    }

    /**
     * @param iterable<array<bool|\DateInterval|\DateTimeInterface|float|int|string|null>> $data
     * @param string $filename
     * @param mixed ...$opts
     * @return bool
     */
    public function writeFile(iterable $data, string $filename, ...$opts): bool
    {
        $this->configure(...$opts);
        $writer = $this->getWriter();

        $writer->openToFile($filename);
        $this->setSheetView($writer);
        foreach ($data as $row) {
            $writer->addRow(Row::fromValues(array_values($row)));
        }
        $writer->close();
        return true;
    }

    /**
     * @param iterable<array<bool|\DateInterval|\DateTimeInterface|float|int|string|null>> $data
     * @param string $filename
     * @param mixed ...$opts
     * @return void
     */
    public function output(iterable $data, string $filename, ...$opts): void
    {
        $this->configure(...$opts);
        $writer = $this->getWriter();

        $writer->openToBrowser($filename);
        $this->setSheetView($writer);
        foreach ($data as $row) {
            $writer->addRow(Row::fromValues(array_values($row)));
        }
        $writer->close();
    }
}
