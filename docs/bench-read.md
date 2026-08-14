# Read Benchmark Results

These benchmarks measure the time it takes to read files using the different adapters.

Since fixed setup overhead (like creating temp streams or evaluating initial logic) can artificially skew results on very small datasets, we provide benchmarks for both small and large data volumes.

## 50K Rows (Large Dataset)

This scenario reflects real-world performance where setup overhead becomes negligible compared to the processing loop. PhpSpreadsheet is omitted here due to extreme execution times.

### CSV

```
LeKoala\SpreadCompat\Csv\Native : 0.0765
LeKoala\SpreadCompat\Csv\League : 0.1332
LeKoala\SpreadCompat\Csv\OpenSpout : 0.2547
```

### XLSX

```
LeKoala\SpreadCompat\Xlsx\Native : 0.3964
LeKoala\SpreadCompat\Xlsx\Xlswriter : 0.4233
LeKoala\SpreadCompat\Xlsx\Simple : 0.8936
LeKoala\SpreadCompat\Xlsx\OpenSpout : 2.7753
```

### ODS

```
LeKoala\SpreadCompat\Ods\Native : 0.4698
LeKoala\SpreadCompat\Ods\OpenSpout : 1.5858
```

## 2.5K Rows (Small Dataset)

In very small datasets, libraries with fewer features (and thus less setup logic) may briefly appear slightly faster, even if their inner loops are technically less optimized.

### CSV (2.5K)

```
LeKoala\SpreadCompat\Csv\Native : 0.0034
LeKoala\SpreadCompat\Csv\OpenSpout : 0.0129
LeKoala\SpreadCompat\Csv\League : 0.0173
LeKoala\SpreadCompat\Csv\PhpSpreadsheet : 0.2687
```

### XLSX (2.5K)

```
LeKoala\SpreadCompat\Xlsx\Native : 0.0203
LeKoala\SpreadCompat\Xlsx\Xlswriter : 0.0254
LeKoala\SpreadCompat\Xlsx\Simple : 0.04
LeKoala\SpreadCompat\Xlsx\OpenSpout : 0.1434
LeKoala\SpreadCompat\Xlsx\PhpSpreadsheet : 0.3184
```

### ODS (2.5K)

```
LeKoala\SpreadCompat\Ods\Native : 0.0242
LeKoala\SpreadCompat\Ods\OpenSpout : 0.0904
```

