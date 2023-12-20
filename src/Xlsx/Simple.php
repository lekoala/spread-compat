<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Xlsx;

use Generator;
use RuntimeException;
use Shuchkin\SimpleXLSX;
use Shuchkin\SimpleXLSXGen;
use LeKoala\SpreadCompat\Xlsx\XlsxAdapter;

/**
 * This class allows you to read and write csv easily if you
 * don't have League or OpenSpout installed
 */
class Simple extends XlsxAdapter
{
    public function readString(
        string $contents,
        ...$opts
    ): Generator {
        $file = tmpfile();
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
        $xlsx = SimpleXLSX::parse($filename);
        if (!$xlsx) {
            throw new RuntimeException("Parse error: " . (string)SimpleXLSX::parseError());
        }
        foreach ($xlsx->readRows() as $r) {
            if (empty($r) || $r[0] === "") {
                continue;
            }
            yield $r;
        }
    }

    protected function getWriter(iterable $data): SimpleXLSXGen
    {
        if (!is_array($data)) {
            $data = iterator_to_array($data);
        }
        $xlsx = SimpleXLSXGen::fromArray($data);
        if ($this->creator) {
            $xlsx->setAuthor($this->creator);
        }
        if ($this->autofilter) {
            $xlsx->autoFilter($this->autofilter);
        }
        if ($this->freezePane) {
            $xlsx->freezePanes($this->freezePane);
        }
        return $xlsx;
    }

    public function writeString(
        iterable $data,
        ...$opts
    ): string {
        $file = tmpfile();
        $filename = stream_get_meta_data($file)['uri'];
        $this->writeFile($data, $filename);
        $contents = file_get_contents($filename);
        unlink($filename);
        return $contents;
    }

    public function writeFile(
        iterable $data,
        string $filename,
        ...$opts
    ): bool {
        $this->configure(...$opts);
        $xlsx = $this->getWriter($data);
        $xlsx->saveAs($filename);
        return true;
    }

    public function output(
        iterable $data,
        string $filename,
        ...$opts
    ): void {
        $this->configure(...$opts);
        $xlsx = $this->getWriter($data);
        $xlsx->downloadAs($filename);
    }
}