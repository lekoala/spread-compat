# Write Benchmark Results

These benchmarks measure the time it takes to write files using the different adapters.

Since fixed setup overhead (like creating temp streams or evaluating initial logic) can artificially skew results on very small datasets, we provide benchmarks for both small and large data volumes.

## 50K Rows (Large Dataset)

This scenario reflects real-world performance where setup overhead becomes negligible compared to the processing loop. PhpSpreadsheet is omitted here due to extreme execution times.

### CSV

```
LeKoala\SpreadCompat\Csv\Native : 0.1146
LeKoala\SpreadCompat\Csv\League : 0.1591
LeKoala\SpreadCompat\Csv\OpenSpout : 0.3272
```

### XLSX

```
LeKoala\SpreadCompat\Xlsx\Xlswriter : 0.1325
LeKoala\SpreadCompat\Xlsx\Native : 0.2545
LeKoala\SpreadCompat\Xlsx\Simple : 0.7063
LeKoala\SpreadCompat\Xlsx\OpenSpout : 1.0415
```

### ODS

```
LeKoala\SpreadCompat\Ods\Native : 0.2087
LeKoala\SpreadCompat\Ods\OpenSpout : 1.424
```

## 2.5K Rows (Small Dataset)

In very small datasets, libraries with fewer features (and thus less setup logic) may briefly appear slightly faster, even if their inner loops are technically less optimized.

### CSV (2.5K)

```
LeKoala\SpreadCompat\Csv\Native : 0.0061
LeKoala\SpreadCompat\Csv\League : 0.0083
LeKoala\SpreadCompat\Csv\OpenSpout : 0.0161
LeKoala\SpreadCompat\Csv\PhpSpreadsheet : 0.253
```

### XLSX (2.5K)

```
LeKoala\SpreadCompat\Xlsx\Xlswriter : 0.0126
LeKoala\SpreadCompat\Xlsx\Native : 0.0131
LeKoala\SpreadCompat\Xlsx\Simple : 0.0348
LeKoala\SpreadCompat\Xlsx\OpenSpout : 0.0958
LeKoala\SpreadCompat\Xlsx\PhpSpreadsheet : 0.3304
```

### ODS (2.5K)

```
LeKoala\SpreadCompat\Ods\Native : 0.0115
LeKoala\SpreadCompat\Ods\OpenSpout : 0.12
```

