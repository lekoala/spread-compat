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
use LeKoala\SpreadCompat\Xlsx\Xlswriter;
use LeKoala\SpreadCompat\Ods\Native as OdsNative;
use LeKoala\SpreadCompat\Ods\OpenSpout as OdsOpenSpout;

require dirname(__DIR__) . '/vendor/autoload.php';

$sizes = [
    '50K'  => 50000,
    '2.5K' => 2500,
];

$xlsxWriters = [
    XlsxNative::class,
    Simple::class,
    XlsxOpenSpout::class,
    XlsxPhpSpreadsheet::class,
];
if (extension_loaded('xlswriter')) {
    array_unshift($xlsxWriters, Xlswriter::class);
}

$formats = [
    'csv' => [
        League::class,
        OpenSpout::class,
        Native::class,
        PhpSpreadsheet::class
    ],
    'xlsx' => $xlsxWriters,
    'ods' => [
        OdsOpenSpout::class,
        OdsNative::class,
    ]
];

$reps = 3;

echo "# Write Benchmark Results" . PHP_EOL . PHP_EOL;
echo "These benchmarks measure the time it takes to write files using the different adapters." . PHP_EOL . PHP_EOL;
echo "Since fixed setup overhead (like creating temp streams or evaluating initial logic) can artificially skew results on very small datasets, we provide benchmarks for both small and large data volumes." . PHP_EOL . PHP_EOL;

foreach ($sizes as $sizeName => $rowCount) {
    $label = $sizeName === '50K' ? 'Large Dataset' : 'Small Dataset';
    echo "## $sizeName Rows ($label)" . PHP_EOL . PHP_EOL;
    if ($sizeName === '50K') {
        echo "This scenario reflects real-world performance where setup overhead becomes negligible compared to the processing loop. PhpSpreadsheet is omitted here due to extreme execution times." . PHP_EOL . PHP_EOL;
    } else {
        echo "In very small datasets, libraries with fewer features (and thus less setup logic) may briefly appear slightly faster, even if their inner loops are technically less optimized." . PHP_EOL . PHP_EOL;
    }

    $genData = [];
    foreach (range(1, $rowCount) as $i) {
        $genData[] = [$i, "fname $i", "sname $i", "email-$i@domain.com"];
    }

    $times = [];

    foreach ($formats as $format => $classes) {
        $tempFile = SpreadCompat::getTempFilename() . '.' . $format;
        foreach ($classes as $cl) {
            // Skip PhpSpreadsheet for 50K
            if ($sizeName === '50K' && str_contains($cl, 'PhpSpreadsheet')) {
                continue;
            }

            foreach (range(1, $reps) as $i) {
                $inst = new ($cl);
                try {
                    $st = microtime(true);
                    $inst->writeFile($genData, $tempFile);
                    $et = microtime(true);
                    $diff = $et - $st;
                    $times[$format][$cl][] = $diff;
                } catch (\Exception $e) {
                    // Ignore exceptions for unsupported configurations
                }
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            }
        }
    }

    foreach (['csv', 'xlsx', 'ods'] as $format) {
        if (!isset($times[$format])) {
            continue;
        }
        echo "### " . strtoupper($format);
        if ($sizeName !== '50K') {
            echo " ($sizeName)";
        }
        echo PHP_EOL . PHP_EOL;

        echo '```' . PHP_EOL;
        $results = [];
        foreach ($times[$format] as $class => $runTimes) {
            $averageTime = round(array_sum($runTimes) / count($runTimes), 4);
            $results[$class] = $averageTime;
        }

        uasort($results, fn($a, $b) => $a <=> $b);
        foreach ($results as $class => $averageTime) {
            echo "$class : " . $averageTime . PHP_EOL;
        }
        echo '```' . PHP_EOL . PHP_EOL;
    }
}
