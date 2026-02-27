<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Common;

use Generator;
use LeKoala\SpreadCompat\SpreadCompat;

trait ReadWriteString
{
    public function readString(
        string $contents,
        ...$opts
    ): Generator {
        $filename = SpreadCompat::getTempFilename();
        file_put_contents($filename, $contents);
        yield from $this->readFile($filename, ...$opts);
        unlink($filename);
    }

    public function writeString(
        iterable $data,
        ...$opts
    ): string {
        $filename = SpreadCompat::getTempFilename();
        $this->writeFile($data, $filename, ...$opts);
        $contents = file_get_contents($filename);
        if (!$contents) {
            $contents = "";
        }
        unlink($filename);
        return $contents;
    }
}
