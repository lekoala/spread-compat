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
