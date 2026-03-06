# Read Benchmark Results

These benchmarks measure the time it takes to read files using the different adapters.

Since fixed setup overhead (like creating temp streams or evaluating initial logic) can artificially skew results on very small datasets, we provide benchmarks for both small and large data volumes.

## 50K Rows (Large Dataset)

This scenario reflects real-world performance where setup overhead becomes negligible compared to the processing loop. PhpSpreadsheet is omitted here due to extreme execution times.

### CSV

LeKoala\SpreadCompat\Csv\Native : 0.0760
LeKoala\SpreadCompat\Csv\League : 0.1289
LeKoala\SpreadCompat\Csv\OpenSpout : 0.2548

### XLSX

LeKoala\SpreadCompat\Xlsx\Native : 0.3667
LeKoala\SpreadCompat\Xlsx\Simple : 0.7399

### ODS

LeKoala\SpreadCompat\Ods\Native : 0.4512
LeKoala\SpreadCompat\Ods\OpenSpout : 1.4000

## 2.5K Rows (Small Dataset)

In very small datasets, libraries with fewer features (and thus less setup logic) may briefly appear slightly faster, even if their inner loops are technically less optimized.

### CSV (2.5K)

LeKoala\SpreadCompat\Csv\Native : 0.0037
LeKoala\SpreadCompat\Csv\OpenSpout : 0.0130
LeKoala\SpreadCompat\Csv\League : 0.0164
LeKoala\SpreadCompat\Csv\PhpSpreadsheet : 0.1730

### XLSX (2.5K)

LeKoala\SpreadCompat\Xlsx\Native : 0.0185
LeKoala\SpreadCompat\Xlsx\Simple : 0.0416
LeKoala\SpreadCompat\Xlsx\PhpSpreadsheet : 0.2033

### ODS (2.5K)

LeKoala\SpreadCompat\Ods\Native : 0.0216
LeKoala\SpreadCompat\Ods\OpenSpout : 0.0788
