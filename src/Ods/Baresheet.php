<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Ods;

use Generator;
use LeKoala\Baresheet\OdsReader;
use LeKoala\Baresheet\OdsWriter;

class Baresheet extends OdsAdapter
{
    public function readFile(string $filename, ...$opts): Generator
    {
        $this->configure(...$opts);
        $reader = new OdsReader();
        $reader->assoc = $this->assoc;
        return $reader->readFile($filename);
    }

    public function writeFile(iterable $data, string $filename, ...$opts): bool
    {
        $this->configure(...$opts);
        $writer = new OdsWriter();
        $writer->stream = $this->stream;
        $writer->tempPath = $this->tempPath;
        /** @var iterable<array<\DateTimeInterface|float|int|string|\Stringable|null>> $data */
        return $writer->writeFile($data, $filename);
    }

    public function output(iterable $data, string $filename, ...$opts): void
    {
        $this->configure(...$opts);
        $writer = new OdsWriter();
        $writer->stream = $this->stream;
        $writer->tempPath = $this->tempPath;
        /** @var iterable<array<\DateTimeInterface|float|int|string|\Stringable|null>> $data */
        $writer->output($data, $filename);
    }

    public function writeString(iterable $data, ...$opts): string
    {
        $this->configure(...$opts);
        $writer = new OdsWriter();
        $writer->stream = $this->stream;
        $writer->tempPath = $this->tempPath;
        $writer->headers = $this->headers;
        /** @var iterable<array<\DateTimeInterface|float|int|string|\Stringable|null>> $data */
        return $writer->writeString($data);
    }
}
