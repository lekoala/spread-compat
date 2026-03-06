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
use LeKoala\SpreadCompat\Ods\Native as OdsNative;
use LeKoala\SpreadCompat\Ods\OpenSpout as OdsOpenSpout;

require dirname(__DIR__) . '/vendor/autoload.php';

$sizes = [
    '2.5K' => 2500,
    '50K'  => 50000,
];

$formats = [
    'csv' => [
        League::class,
        OpenSpout::class,
        Native::class,
        PhpSpreadsheet::class
    ],
    'xlsx' => [
        Simple::class,
        XlsxOpenSpout::class,
        XlsxPhpSpreadsheet::class,
        XlsxNative::class,
    ],
    'ods' => [
        OdsOpenSpout::class,
        OdsNative::class,
    ]
];

$reps = 3;

foreach ($sizes as $sizeName => $rowCount) {
    echo "======================================" . PHP_EOL;
    echo "Running $sizeName ($rowCount rows) read benchmark" . PHP_EOL;
    echo "======================================" . PHP_EOL . PHP_EOL;

    $genData = [];
    foreach (range(1, $rowCount) as $i) {
        $genData[] = [$i, "fname $i", "sname $i", "email-$i@domain.com"];
    }

    $times = [];

    foreach ($formats as $format => $classes) {
        // Prepare file using Native (Baresheet) adapter since it's fast
        $tempFile = SpreadCompat::getTempFilename() . '.' . $format;
        $writerClass = null;
        if ($format === 'csv') $writerClass = Native::class;
        if ($format === 'xlsx') $writerClass = XlsxNative::class;
        if ($format === 'ods') $writerClass = OdsNative::class;

        if ($writerClass) {
            $writer = new $writerClass();
            $writer->writeFile($genData, $tempFile);
        }

        foreach ($classes as $cl) {
            // Skip PhpSpreadsheet for 50K
            if ($sizeName === '50K' && str_contains($cl, 'PhpSpreadsheet')) {
                continue;
            }

            foreach (range(1, $reps) as $i) {
                $inst = new ($cl);
                try {
                    $st = microtime(true);
                    $data = iterator_to_array($inst->readFile($tempFile));
                    $et = microtime(true);
                    $diff = $et - $st;
                    $times[$format][$cl][] = $diff;
                } catch (\Throwable $e) {
                    // Ignore exceptions or assertions for unsupported configurations
                }
            }
        }

        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }

    foreach ($times as $format => $dataFormat) {
        echo "Results for $format ($sizeName)" . PHP_EOL . PHP_EOL;

        echo "```" . PHP_EOL;
        $results = [];
        foreach ($dataFormat as $class => $runTimes) {
            $averageTime = round(array_sum($runTimes) / count($runTimes), 4);
            $results[$class] = $averageTime;
        }

        uasort($results, fn($a, $b) => $a <=> $b);
        foreach ($results as $class => $averageTime) {
            echo "$class : " . $averageTime . PHP_EOL;
        }
        echo "```" . PHP_EOL;
        echo PHP_EOL;
    }
}
