<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Xlsx;

use Generator;
use LeKoala\SpreadCompat\SpreadCompat;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\AutoFilter;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Properties;
use RuntimeException;

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
     * Convert an Excel column letter (e.g., "A", "AA", "XFD") to a 0-based index.
     */
    private function columnLetterToIndex(string $letters): int
    {
        $index = 0;
        foreach (str_split(strtoupper($letters)) as $char) {
            $index = $index * 26 + (ord($char) - ord('A') + 1);
        }
        return $index - 1;
    }

    /**
     * Parse an Excel cell reference (e.g., "A1", "AA10") into [columnIndex, row].
     * @return array{int<0, max>, int<1, max>}|null
     */
    private function parseCellReference(string $cell): ?array
    {
        if (!preg_match('/^([A-Z]+)(\d+)$/i', $cell, $matches)) {
            return null;
        }
        $columnIndex = max(0, $this->columnLetterToIndex($matches[1]));
        $row = (int) $matches[2];
        if ($row < 1) {
            return null;
        }
        return [$columnIndex, $row];
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
            $ref = $this->parseCellReference($this->freezePane);
            if ($ref) {
                [$columnIndex, $row] = $ref;
                $column = SpreadCompat::getLetter($columnIndex + 1);
                $sheetView = new SheetView();
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
                $writer->getCurrentSheet()->setSheetView($sheetView);
            }
        }
        if ($this->autofilter) {
            $c = $this->autofilterCoords();
            $autoFilter = new AutoFilter($c[0], $c[1], $c[2], $c[3]);
            $writer->getCurrentSheet()->setAutoFilter($autoFilter);
        }
    }

    /**
     * @return array{int<0, max>, int<1, max>, int<0, max>, int<1, max>}
     */
    public function autofilterCoords(): array
    {
        $parts = explode(":", $this->autofilter ?? "");
        $fromRef = $parts[0];
        $toRef = $parts[1] ?? '';

        $from = $this->parseCellReference($fromRef);
        $to = $this->parseCellReference($toRef);

        if (!$from || !$to) {
            throw new RuntimeException("Invalid autofilter range: {$this->autofilter}");
        }

        return [
            $from[0],
            $from[1],
            $to[0],
            $to[1],
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
