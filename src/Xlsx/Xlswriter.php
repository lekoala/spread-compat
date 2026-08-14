<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Xlsx;

use DateTimeInterface;
use Generator;
use LeKoala\SpreadCompat\SpreadCompat;
use RuntimeException;
use Vtiful\Kernel\Excel;

/**
 * Adapter for the xlswriter PHP C extension (viest/php-ext-xlswriter).
 *
 * The extension writes to files only, using a directory + filename, and offers
 * a constant memory write mode (constMemory) which streams rows to disk.
 * Reading uses a cursor (one row at a time).
 *
 * Value semantics are normalized to match the other adapters:
 *  - datetime cells are returned as date strings (Y-m-d / H:i:s / Y-m-d H:i:s)
 *  - other cells are returned as-is (typed int/float/string/bool)
 */
class Xlswriter extends XlsxAdapter
{
    protected function getMimetype(): string
    {
        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
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
        $excel = new Excel(['path' => dirname($filename)]);
        $excel->openFile(basename($filename))->openSheet();

        /** @var array<int,string> $formatCategories */
        $formatCategories = [];
        $headers = null;
        $width = null;

        while (true) {
            $row = (array) $excel->nextRowWithFormula();
            if ($row === []) {
                break;
            }
            $data = [];
            foreach ($row as $index => $cell) {
                if (!is_array($cell)) {
                    continue;
                }
                $value = $cell['value'];
                if ($cell['type'] === 'datetime' && is_numeric($value)) {
                    $styleId = $cell['style_id'] ?? 0;
                    if (!is_int($styleId)) {
                        $styleId = 0;
                    }
                    if (!isset($formatCategories[$styleId])) {
                        $format = $excel->getStyleFormat($styleId);
                        $category = $format['category'] ?? null;
                        $formatCategories[$styleId] = is_string($category) ? $category : 'datetime';
                    }
                    if ($formatCategories[$styleId] === 'time') {
                        $value = gmdate('H:i:s', (int) $value);
                    } elseif ((int) $value % 86400 === 0) {
                        $value = gmdate('Y-m-d', (int) $value);
                    } else {
                        $value = gmdate('Y-m-d H:i:s', (int) $value);
                    }
                }
                $data[$index] = $value;
            }
            if ($data) {
                // Empty cells are omitted by the reader, restore the gaps
                $data = array_replace(array_fill(0, max(array_keys($data)) + 1, null), $data);
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
                $data = array_combine(
                    $headers,
                    array_slice(array_pad($data, count($headers), null), 0, count($headers))
                );
            } else {
                if ($width === null) {
                    $width = count($data);
                }
                $data = array_slice(array_pad($data, $width, null), 0, $width);
            }
            yield $data;
        }
    }

    /**
     * Parse an Excel cell reference (e.g. "A2", "AA10") into [column, row].
     *
     * @return array{int, int}|null
     */
    private function parseCellReference(string $cell): ?array
    {
        if (!preg_match('/^([A-Z]+)(\d+)$/i', $cell, $matches)) {
            return null;
        }
        $column = 0;
        foreach (str_split(strtoupper($matches[1])) as $char) {
            $column = $column * 26 + (ord($char) - ord('A') + 1);
        }
        $row = (int) $matches[2];
        if ($row < 1) {
            return null;
        }
        return [$column - 1, $row];
    }

    /**
     * Convert a date to an Excel serial number (UTC based).
     */
    private static function dateToExcel(DateTimeInterface $date): float
    {
        return $date->getTimestamp() / 86400 + 25569;
    }

    /**
     * @param iterable<array<\DateTimeInterface|float|int|string|\Stringable|null>> $data
     * @param string $filename
     * @param mixed ...$opts
     * @return bool
     */
    public function writeFile(
        iterable $data,
        string $filename,
        ...$opts
    ): bool {
        $this->configure(...$opts);
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new RuntimeException("Unable to create directory $dir");
            }
        }

        $excel = new Excel(['path' => $dir]);
        $excel = $excel->constMemory(basename($filename));

        if ($this->headers) {
            $excel->header($this->headers);
        }
        if ($this->autofilter) {
            $excel->autoFilter($this->autofilter);
        }
        if ($this->freezePane) {
            $ref = $this->parseCellReference($this->freezePane);
            if ($ref) {
                [$column, $row] = $ref;
                $excel->freezePanes($row, $column);
            }
        }

        $properties = [];
        if ($this->creator !== null) {
            $properties['author'] = $this->creator;
        }
        if ($this->title !== null) {
            $properties['title'] = $this->title;
        }
        if ($this->subject !== null) {
            $properties['subject'] = $this->subject;
        }
        if ($this->keywords !== null) {
            $properties['keywords'] = $this->keywords;
        }
        if ($properties) {
            $excel->setProperties($properties);
        }

        foreach ($data as $row) {
            $row = array_values(
                array_map(
                    static function ($value) {
                        if ($value instanceof DateTimeInterface) {
                            return self::dateToExcel($value);
                        }
                        return is_scalar($value) || $value === null ? $value : (string) $value;
                    },
                    $row
                )
            );
            $excel->data([$row]);
        }

        $excel->output();
        return true;
    }

    /**
     * @param iterable<array<\DateTimeInterface|float|int|string|\Stringable|null>> $data
     * @param string $filename
     * @param mixed ...$opts
     * @return void
     */
    public function output(
        iterable $data,
        string $filename,
        ...$opts
    ): void {
        $this->configure(...$opts);
        $tmpFile = SpreadCompat::getTempFilename() . '.xlsx';
        $this->writeFile($data, $tmpFile, ...$opts);

        SpreadCompat::outputHeaders($this->getMimetype(), $filename);
        if (ob_get_level() > 0) {
            ob_clean();
        }
        readfile($tmpFile);
        unlink($tmpFile);
    }
}
