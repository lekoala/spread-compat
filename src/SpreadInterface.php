<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat;

use Generator;

interface SpreadInterface
{
    public function readString(
        string $contents,
        ...$opts
    ): Generator;

    public function readFile(
        string $filename,
        ...$opts
    ): Generator;

    public function writeString(
        iterable $data,
        ...$opts
    ): string;

    public function writeFile(
        iterable $data,
        string $filename,
        ...$opts
    ): bool;

    public function output(
        iterable $data,
        string $filename,
        ...$opts
    ): void;
}
