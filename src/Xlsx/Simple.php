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

        /** @var SimpleXLSX|null $xlsx */
        $xlsx = SimpleXLSX::parse($filename);
        if (!$xlsx) {
            $err = SimpleXLSX::parseError();
            if (!is_string($err)) {
                $err = "unknown error";
            }
            throw new RuntimeException("Parse error: $err");
        }
        $headers = null;

        /** @var iterable<array<string>> $rows */
        $rows = $xlsx->readRows();
        foreach ($rows as $r) {
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

        /** @var SimpleXLSXGen|null $xlsx */
        $xlsx = SimpleXLSXGen::fromArray($data);
        if (!$xlsx) {
            throw new RuntimeException("Read from array error");
        }
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

    /**
     * @param iterable<array<float|int|string|\Stringable|null>> $data
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
        $xlsx = $this->getWriter($data);
        $xlsx->saveAs($filename);
        return true;
    }

    /**
     * @param iterable<array<float|int|string|\Stringable|null>> $data
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
        $xlsx = $this->getWriter($data);
        $xlsx->downloadAs($filename);
    }
}
