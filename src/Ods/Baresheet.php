<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Ods;

use Generator;
use LeKoala\Baresheet\OdsReader;
use LeKoala\Baresheet\OdsWriter;
use LeKoala\SpreadCompat\StreamWriterInterface;

class Baresheet extends OdsAdapter implements StreamWriterInterface
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
        $this->configureWriter($writer);
        /** @var iterable<array<\DateTimeInterface|float|int|string|\Stringable|null>> $data */
        return $writer->writeFile($data, $filename);
    }

    public function output(iterable $data, string $filename, ...$opts): void
    {
        $this->configure(...$opts);
        $writer = new OdsWriter();
        $this->configureWriter($writer);
        /** @var iterable<array<\DateTimeInterface|float|int|string|\Stringable|null>> $data */
        $writer->output($data, $filename);
    }

    public function writeString(iterable $data, ...$opts): string
    {
        $this->configure(...$opts);
        $writer = new OdsWriter();
        $this->configureWriter($writer);
        /** @var iterable<array<\DateTimeInterface|float|int|string|\Stringable|null>> $data */
        return $writer->writeString($data);
    }

    public function writeStream(iterable $data, ...$opts)
    {
        $this->configure(...$opts);
        $writer = new OdsWriter();
        $this->configureWriter($writer);
        /** @var iterable<array<\DateTimeInterface|float|int|string|\Stringable|null>> $data */
        return $writer->writeStream($data);
    }

    private function configureWriter(OdsWriter $writer): void
    {
        $writer->stream = $this->stream;
        $writer->tempPath = $this->tempPath;
        $writer->headers = $this->headers;
        $writer->meta = $this->buildMeta();
    }

    /**
     * @return array<string, string>
     */
    private function buildMeta(): array
    {
        $meta = [];
        foreach (['creator', 'title', 'subject', 'keywords', 'description', 'language'] as $key) {
            if ($this->$key !== null) {
                $meta[$key] = $this->$key;
            }
        }
        return $meta;
    }
}
