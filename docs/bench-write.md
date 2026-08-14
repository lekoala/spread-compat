# Write Benchmark Results

These benchmarks measure the time it takes to write files using the different adapters.

Since fixed setup overhead (like creating temp streams or evaluating initial logic) can artificially skew results on very small datasets, we provide benchmarks for both small and large data volumes.

## 50K Rows (Large Dataset)

This scenario reflects real-world performance where setup overhead becomes negligible compared to the processing loop. PhpSpreadsheet is omitted here due to extreme execution times.

### CSV

LeKoala\SpreadCompat\Csv\Native : 0.1088
LeKoala\SpreadCompat\Csv\League : 0.1463
LeKoala\SpreadCompat\Csv\OpenSpout : 0.2999

### XLSX

LeKoala\SpreadCompat\Xlsx\Xlswriter : 0.1287
LeKoala\SpreadCompat\Xlsx\Native : 0.2433
LeKoala\SpreadCompat\Xlsx\Simple : 1.5552
LeKoala\SpreadCompat\Xlsx\OpenSpout : 2.0479

### ODS

LeKoala\SpreadCompat\Ods\Native : 0.4422
LeKoala\SpreadCompat\Ods\OpenSpout : 2.6898

## 2.5K Rows (Small Dataset)

In very small datasets, libraries with fewer features (and thus less setup logic) may briefly appear slightly faster, even if their inner loops are technically less optimized.

### CSV (2.5K)

LeKoala\SpreadCompat\Csv\Native : 0.0143
LeKoala\SpreadCompat\Csv\League : 0.0197
LeKoala\SpreadCompat\Csv\OpenSpout : 0.0385
LeKoala\SpreadCompat\Csv\PhpSpreadsheet : 0.6535

### XLSX (2.5K)

LeKoala\SpreadCompat\Xlsx\Xlswriter : 0.025
LeKoala\SpreadCompat\Xlsx\Native : 0.0359
LeKoala\SpreadCompat\Xlsx\Simple : 0.0897
LeKoala\SpreadCompat\Xlsx\OpenSpout : 0.1654
LeKoala\SpreadCompat\Xlsx\PhpSpreadsheet : 0.7859

### ODS (2.5K)

LeKoala\SpreadCompat\Ods\Native : 0.0245
LeKoala\SpreadCompat\Ods\OpenSpout : 0.2087

