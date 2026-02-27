<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat;

use Generator;

interface SpreadInterface
{
    /**
     * @param string $contents
     * @param mixed ...$opts
     * @return Generator<mixed>
     */
    public function readString(
        string $contents,
        ...$opts
    ): Generator;

    /**
     * @param string $filename
     * @param mixed ...$opts
     * @return Generator<mixed>
     */
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
