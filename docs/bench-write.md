# Write Benchmark Results

These benchmarks measure the time it takes to write files using the different adapters.

Since fixed setup overhead (like creating temp streams or evaluating initial logic) can artificially skew results on very small datasets, we provide benchmarks for both small and large data volumes.

## 50K Rows (Large Dataset)

This scenario reflects real-world performance where setup overhead becomes negligible compared to the processing loop. PhpSpreadsheet is omitted here due to extreme execution times.

### CSV

LeKoala\SpreadCompat\Csv\Native : 0.1074
LeKoala\SpreadCompat\Csv\League : 0.1478
LeKoala\SpreadCompat\Csv\OpenSpout : 0.317

### XLSX

LeKoala\SpreadCompat\Xlsx\Native : 0.6214
LeKoala\SpreadCompat\Xlsx\Simple : 0.6784
LeKoala\SpreadCompat\Xlsx\OpenSpout : 1.7409

### ODS

LeKoala\SpreadCompat\Ods\Native : 1.4650
LeKoala\SpreadCompat\Ods\OpenSpout : 2.1411

## 2.5K Rows (Small Dataset)

In very small datasets, libraries with fewer features (and thus less setup logic) may briefly appear slightly faster, even if their inner loops are technically less optimized.

### CSV (2.5K)

LeKoala\SpreadCompat\Csv\Native : 0.0060
LeKoala\SpreadCompat\Csv\League : 0.0081
LeKoala\SpreadCompat\Csv\OpenSpout : 0.0176
LeKoala\SpreadCompat\Csv\PhpSpreadsheet : 0.1758

### XLSX (2.5K)

LeKoala\SpreadCompat\Xlsx\Simple : 0.0335
LeKoala\SpreadCompat\Xlsx\Native : 0.0608
LeKoala\SpreadCompat\Xlsx\OpenSpout : 0.1331
LeKoala\SpreadCompat\Xlsx\PhpSpreadsheet : 0.2499

### ODS (2.5K)

LeKoala\SpreadCompat\Ods\Native : 0.1171
LeKoala\SpreadCompat\Ods\OpenSpout : 0.1567
