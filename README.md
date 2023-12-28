> Easily manipulate PhpSpreadsheet, OpenSpout and League CSV

## Supported packages

OpenSpout: fast csv and excel export
https://github.com/openspout/openspout

League CSV: fast csv export
https://github.com/thephpleague/csv

PhpSpreadsheet: slow excel and csv export, but more features
https://github.com/PHPOffice/PhpSpreadsheet

Native php: fast but limited features

SimpleXLSX

## Using the facade

While you can use individual adapters, it's very likely you don't want to bother too much
how your files are read and written. This package provides a simple facade with static
methods in order to read and write files.

Please note that read methods return a `Generator`. If you want an array, you need to use `iterator_to_array`.

```php
$data = iterator_to_array(SpreadCompat::read('myfile.csv'));
```

## Using named arguments

This package accepts options using ...opts, this means you can freely use named arguments or pass an array.

```php
$data = iterator_to_array(SpreadCompat::read('myfile.csv', assoc: true));
```

## Worksheets

This package support only 1 worksheet, as it is meant to be able to replace csv by xlsx or vice versa

## Benchmarks

Since we can compare our solutions, there is a built in bench.php script that give the following results on my machine

Reading a file with 5000 rows:

    Results for csv
    LeKoala\SpreadCompat\Csv\League : 0.031
    LeKoala\SpreadCompat\Csv\OpenSpout : 0.0916
    LeKoala\SpreadCompat\Csv\Native : 0.0075
    LeKoala\SpreadCompat\Csv\PhpSpreadsheet : 3.7089

    Results for xlsx
    LeKoala\SpreadCompat\Xlsx\Simple : 0.1551
    LeKoala\SpreadCompat\Xlsx\OpenSpout : 0.8315
    LeKoala\SpreadCompat\Xlsx\PhpSpreadsheet : 0.7036

For reading, the native + simple combo seems to be the most efficient

Write a file with 1000 rows:

    Results for csv
    LeKoala\SpreadCompat\Csv\League : 0.0116
    LeKoala\SpreadCompat\Csv\OpenSpout : 0.0189
    LeKoala\SpreadCompat\Csv\Native : 0.0066
    LeKoala\SpreadCompat\Csv\PhpSpreadsheet : 0.1331

    Results for xlsx
    LeKoala\SpreadCompat\Xlsx\Simple : 0.0304
    LeKoala\SpreadCompat\Xlsx\OpenSpout : 0.1228
    LeKoala\SpreadCompat\Xlsx\PhpSpreadsheet : 0.2446

For writing, the native + simple combo seems to be the most efficient
