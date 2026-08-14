<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat;

/**
 * Optional capability for adapters that can write to a stream resource.
 * The facade throws when the selected adapter does not support this capability.
 */
interface StreamWriterInterface
{
    /**
     * @param iterable<array<mixed>> $data
     * @param mixed ...$opts
     * @return resource The opened stream containing the data. It is the caller's responsibility to close it.
     */
    public function writeStream(
        iterable $data,
        ...$opts
    );
}
