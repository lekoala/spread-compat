<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use LeKoala\SpreadCompat\SpreadCompat;

$data = [
    ['name', 'age'],
    ['john', 42],
    ['jane', 37],
];

foreach (['csv', 'xlsx', 'ods'] as $ext) {
    $string = SpreadCompat::writeString($data, $ext);
    if ($string === '') {
        fwrite(STDERR, "FAIL: writeString($ext) returned empty output\n");
        exit(1);
    }
    $rows = iterator_to_array(SpreadCompat::readString($string, $ext, assoc: true));
    if (count($rows) !== 2 || $rows[0]['name'] !== 'john' || (string) $rows[1]['age'] !== '37') {
        fwrite(STDERR, "FAIL: $ext round-trip returned unexpected data\n");
        exit(1);
    }
}

fwrite(STDOUT, "OK: Baresheet-only smoke test passed\n");