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
    public function readFile(
        string $filename,
        ...$opts
    ): Generator {
        $this->configure(...$opts);
        $xlsx = SimpleXLSX::parse($filename);
        if (!$xlsx) {
            throw new RuntimeException("Parse error: " . (string)SimpleXLSX::parseError());
        }
        $headers = null;
        foreach ($xlsx->readRows() as $r) {
            if (empty($r) || $r[0] === "") {
                continue;
            }
            if ($this->assoc) {
                if ($headers === null) {
                    $headers = $r;
                    continue;
                }
                $r = array_combine($headers, $r);
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
