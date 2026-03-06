<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Ods;

use Generator;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\ODS\Reader;
use OpenSpout\Writer\ODS\Writer;

class OpenSpout extends OdsAdapter
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
        $options = new \OpenSpout\Reader\ODS\Options();

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
        $options = new \OpenSpout\Writer\ODS\Options();
        $writer = new Writer($options);
        return $writer;
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
        foreach ($data as $row) {
            $writer->addRow(Row::fromValues(array_values($row)));
        }
        $writer->close();
    }
}
