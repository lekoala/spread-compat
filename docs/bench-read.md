# Read Benchmark Results

These benchmarks measure the time it takes to read files using the different adapters.

Since fixed setup overhead (like creating temp streams or evaluating initial logic) can artificially skew results on very small datasets, we provide benchmarks for both small and large data volumes.

## 50K Rows (Large Dataset)

This scenario reflects real-world performance where setup overhead becomes negligible compared to the processing loop. PhpSpreadsheet is omitted here due to extreme execution times.

### CSV

LeKoala\SpreadCompat\Csv\Native : 0.0726
LeKoala\SpreadCompat\Csv\League : 0.1258
LeKoala\SpreadCompat\Csv\OpenSpout : 0.2333

### XLSX

LeKoala\SpreadCompat\Xlsx\Native : 0.3998
LeKoala\SpreadCompat\Xlsx\Xlswriter : 0.4015
LeKoala\SpreadCompat\Xlsx\Simple : 0.7744
LeKoala\SpreadCompat\Xlsx\OpenSpout : 4.5792

### ODS

LeKoala\SpreadCompat\Ods\Native : 0.978
LeKoala\SpreadCompat\Ods\OpenSpout : 4.5854

## 2.5K Rows (Small Dataset)

In very small datasets, libraries with fewer features (and thus less setup logic) may briefly appear slightly faster, even if their inner loops are technically less optimized.

### CSV (2.5K)

LeKoala\SpreadCompat\Csv\Native : 0.0066
LeKoala\SpreadCompat\Csv\League : 0.0206
LeKoala\SpreadCompat\Csv\OpenSpout : 0.0266
LeKoala\SpreadCompat\Csv\PhpSpreadsheet : 0.5874

### XLSX (2.5K)

LeKoala\SpreadCompat\Xlsx\Native : 0.0445
LeKoala\SpreadCompat\Xlsx\Xlswriter : 0.0507
LeKoala\SpreadCompat\Xlsx\Simple : 0.09
LeKoala\SpreadCompat\Xlsx\OpenSpout : 0.377
LeKoala\SpreadCompat\Xlsx\PhpSpreadsheet : 0.642

### ODS (2.5K)

LeKoala\SpreadCompat\Ods\Native : 0.0483
LeKoala\SpreadCompat\Ods\OpenSpout : 0.2473

