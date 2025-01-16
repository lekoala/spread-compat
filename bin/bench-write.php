<?php

use LeKoala\SpreadCompat\Csv\League;
use LeKoala\SpreadCompat\Csv\Native;
use LeKoala\SpreadCompat\Csv\OpenSpout;
use LeKoala\SpreadCompat\Csv\PhpSpreadsheet;
use LeKoala\SpreadCompat\SpreadCompat;
use LeKoala\SpreadCompat\Xlsx\Native as XlsxNative;
use LeKoala\SpreadCompat\Xlsx\PhpSpreadsheet as XlsxPhpSpreadsheet;
use LeKoala\SpreadCompat\Xlsx\OpenSpout as XlsxOpenSpout;
use LeKoala\SpreadCompat\Xlsx\Simple;

require dirname(__DIR__) . '/vendor/autoload.php';

$largeCsv = SpreadCompat::getTempFilename();
$largeXlsx = SpreadCompat::getTempFilename();

$genData = [];
foreach (range(1, 2500) as $i) {
    $genData[] = [$i, "fname $i", "sname $i", "email-$i@domain.com"];
}

$csv = [
    League::class,
    OpenSpout::class,
    Native::class,
    PhpSpreadsheet::class
];

$xlsx = [
    Simple::class,
    XlsxOpenSpout::class,
    XlsxPhpSpreadsheet::class,
    XlsxNative::class,
];

$reps = 5;

$times = [];
foreach ($csv as $cl) {
    foreach (range(1, $reps) as $i) {
        /** @var \LeKoala\SpreadCompat\Csv\CsvAdapter $inst */
        $inst = new ($cl);

        $st = microtime(true);
        $inst->writeFile($genData, $largeCsv);
        $et = microtime(true);
        $diff = $et - $st;
        $times['csv'][$cl][] = $diff;
    }
}

foreach ($xlsx as $cl) {
    foreach (range(1, $reps) as $i) {
        /** @var \LeKoala\SpreadCompat\Xlsx\XlsxAdapter $inst */
        $inst = new ($cl);

        try {
            $st = microtime(true);
            $inst->writeFile($genData, $largeXlsx);
            $et = microtime(true);
            $diff = $et - $st;
            $times['xlsx'][$cl][] = $diff;
        } catch (Exception $e) {
        }
    }
}

foreach ($times as $format => $dataFormat) {
    echo "Results for $format" . PHP_EOL . PHP_EOL;

    $results = [];
    foreach ($dataFormat as $class => $times) {
        $averageTime = round(array_sum($times) / count($times), 4);
        $results[$class] = $averageTime;
    }

    uasort($results, fn($a, $b) => $a <=> $b);
    foreach ($results as $class => $averageTime) {
        echo "$class : " . $averageTime . PHP_EOL;
    }

    echo PHP_EOL;
}
