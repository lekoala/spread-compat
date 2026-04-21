<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Csv;

use Exception;
use Generator;
use LeKoala\SpreadCompat\SpreadCompat;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\CSV\Reader;
use OpenSpout\Writer\CSV\Writer;

class OpenSpout extends CsvAdapter
{
    /**
     * @param string $contents
     * @param mixed ...$opts
     * @return Generator<mixed>
     */
    public function readString(
        string $contents,
        ...$opts
    ): Generator {
        $filename = SpreadCompat::getTempFilename();
        file_put_contents($filename, $contents);
        try {
            yield from $this->readFile($filename, ...$opts);
        } finally {
            unlink($filename);
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
        $this->configureSeparator($filename);
        $options = $this->createReaderOptions();
        $headers = null;
        //TODO: support escape

        $reader = new Reader($options);
        $reader->open($filename);
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $data = $row->toArray();
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
        $reader->close();
    }

    /**
     * @param resource $stream
     */
    /**
     * @param resource $stream
     * @param mixed ...$opts
     * @return Generator<mixed>
     */
    public function readStream($stream, ...$opts): Generator
    {
        //@link https://github.com/openspout/openspout/issues/71
        throw new Exception("OpenSpout doesn't support streams");
    }

    protected function getWriter(): Writer
    {
        $options = $this->createWriterOptions();
        $writer = new Writer($options);
        return $writer;
    }

    /**
     * Create CSV reader options compatible with OpenSpout v4 and v5.
     */
    private function createReaderOptions(): \OpenSpout\Reader\CSV\Options
    {
        $class = new \ReflectionClass(\OpenSpout\Reader\CSV\Options::class);
        $ctor = $class->getConstructor();

        if ($ctor && $ctor->getNumberOfParameters() > 0) {
            $args = [];
            foreach ($ctor->getParameters() as $param) {
                $name = strtolower(str_replace('_', '', $param->getName()));
                if ($name === 'fielddelimiter') {
                    $args[] = $this->getSeparator();
                } elseif ($name === 'fieldenclosure') {
                    $args[] = $this->enclosure;
                } elseif ($name === 'encoding') {
                    if ($this->inputEncoding) {
                        $args[] = $this->getInputEncoding() ?? mb_internal_encoding();
                    } elseif ($param->isDefaultValueAvailable()) {
                        $args[] = $param->getDefaultValue();
                    } else {
                        $args[] = null;
                    }
                } else {
                    if ($param->isDefaultValueAvailable()) {
                        $args[] = $param->getDefaultValue();
                    } else {
                        $args[] = null;
                    }
                }
            }

            /** @var \OpenSpout\Reader\CSV\Options $options */
            $options = $class->newInstanceArgs($args);
        } else {
            /** @var \OpenSpout\Reader\CSV\Options $options */
            $options = $class->newInstance();
            // This branch is only used with OpenSpout v4, where options are
            // mutable public properties. In v5 they are readonly and this
            // code path is not executed; PHPStan still sees the assignments.
            /** @phpstan-ignore-next-line readonly property (v5) written for v4 compatibility */
            $options->FIELD_DELIMITER = $this->getSeparator();
            /** @phpstan-ignore-next-line readonly property (v5) written for v4 compatibility */
            $options->FIELD_ENCLOSURE = $this->enclosure;
            if ($this->inputEncoding) {
                /** @phpstan-ignore-next-line readonly property (v5) written for v4 compatibility */
                $options->ENCODING = $this->getInputEncoding() ?? mb_internal_encoding();
            }
        }

        return $options;
    }

    /**
     * Create CSV writer options compatible with OpenSpout v4 and v5, using
     * mutable public properties when available, and constructor args for the
     * newer immutable/readonly API.
     */
    private function createWriterOptions(): \OpenSpout\Writer\CSV\Options
    {
        $options = new \OpenSpout\Writer\CSV\Options();

        // In v4, options are configured via public mutable properties.
        $prop = new \ReflectionProperty($options, 'FIELD_DELIMITER');
        if (!$prop->isReadOnly()) {
            /** @phpstan-ignore-next-line readonly property (v5) written for v4 compatibility */
            $options->FIELD_DELIMITER = $this->getSeparator();
            /** @phpstan-ignore-next-line readonly property (v5) written for v4 compatibility */
            $options->FIELD_ENCLOSURE = $this->enclosure;
            /** @phpstan-ignore-next-line readonly property (v5) written for v4 compatibility */
            $options->SHOULD_ADD_BOM = $this->bom;

            return $options;
        }

        // In v5, properties are readonly and configured via the constructor.
        $class = new \ReflectionClass(\OpenSpout\Writer\CSV\Options::class);
        $ctor = $class->getConstructor();
        $args = [];
        if ($ctor) {
            foreach ($ctor->getParameters() as $param) {
                $name = strtolower(str_replace('_', '', $param->getName()));
                if ($name === 'fielddelimiter') {
                    $args[] = $this->getSeparator();
                } elseif ($name === 'fieldenclosure') {
                    $args[] = $this->enclosure;
                } elseif ($name === 'shouldaddbom') {
                    $args[] = $this->bom;
                } else {
                    if ($param->isDefaultValueAvailable()) {
                        $args[] = $param->getDefaultValue();
                    } else {
                        $args[] = null;
                    }
                }
            }
        }

        /** @var \OpenSpout\Writer\CSV\Options $options */
        $options = $class->newInstanceArgs($args);

        return $options;
    }

    /**
     * @param iterable<array<bool|\DateInterval|\DateTimeInterface|float|int|string|null>> $data
     * @param mixed ...$opts
     * @return string
     */
    public function writeString(iterable $data, ...$opts): string
    {
        $this->configure(...$opts);
        $filename = SpreadCompat::getTempFilename();
        try {
            $this->writeFile($data, $filename);
            $contents = file_get_contents($filename);
            if (!$contents) {
                $contents = "";
            }
        } finally {
            unlink($filename);
        }
        return $contents;
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

        $outputEncoding = $this->getOutputEncoding();
        $inputEncoding = mb_internal_encoding();
        $doEncode = $outputEncoding && $outputEncoding !== $inputEncoding;
        $doEscape = $this->escapeFormulas;

        $writer->openToFile($filename);
        if (!empty($this->headers)) {
            $headers = $this->headers;
            if ($doEscape) {
                $headers = $this->escapeRow($headers);
            }
            if ($doEncode) {
                $headers = $this->encodeRow($headers, $outputEncoding, $inputEncoding);
            }
            $writer->addRow(Row::fromValues(array_values($headers)));
        }
        foreach ($data as $row) {
            if ($doEscape) {
                $row = $this->escapeRow($row);
            }
            if ($doEncode) {
                $row = $this->encodeRow($row, $outputEncoding, $inputEncoding);
            }
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

        $outputEncoding = $this->getOutputEncoding();
        $inputEncoding = mb_internal_encoding();
        $doEncode = $outputEncoding && $outputEncoding !== $inputEncoding;
        $doEscape = $this->escapeFormulas;

        if ($outputEncoding && $outputEncoding !== 'UTF-8') {
            SpreadCompat::outputHeaders('text/csv; charset=' . $outputEncoding, $filename);
            $writer->openToFile('php://output');
        } else {
            $writer->openToBrowser($filename);
        }

        if (!empty($this->headers)) {
            $headers = $this->headers;
            if ($doEscape) {
                $headers = $this->escapeRow($headers);
            }
            if ($doEncode) {
                $headers = $this->encodeRow($headers, $outputEncoding, $inputEncoding);
            }
            $writer->addRow(Row::fromValues(array_values($headers)));
        }
        foreach ($data as $row) {
            if ($doEscape) {
                $row = $this->escapeRow($row);
            }
            if ($doEncode) {
                $row = $this->encodeRow($row, $outputEncoding, $inputEncoding);
            }
            $writer->addRow(Row::fromValues(array_values($row)));
        }
        $writer->close();
    }

    /**
     * @template T of array<mixed>
     * @param T $row
     * @param string|null $outputEncoding
     * @param string|null $inputEncoding
     * @return T
     */
    protected function encodeRow(array $row, ?string $outputEncoding = null, ?string $inputEncoding = null): array
    {
        if ($outputEncoding === null) {
            $outputEncoding = $this->getOutputEncoding();
        }
        if (!$outputEncoding) {
            return $row;
        }
        if ($inputEncoding === null) {
            $inputEncoding = mb_internal_encoding();
        }
        if ($inputEncoding === $outputEncoding) {
            return $row;
        }
        foreach ($row as &$cell) {
            if (is_string($cell)) {
                $cell = mb_convert_encoding($cell, $outputEncoding, $inputEncoding);
            }
        }
        /** @var T $row */
        return $row;
    }
}
