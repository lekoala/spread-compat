<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat;

/**
 * Optional capability for adapters that can write to a stream resource.
 * Adapters that do not support stream writing simply don't implement this
 * interface and are routed through the file/string APIs instead.
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
