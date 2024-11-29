> Easily manipulate PhpSpreadsheet, OpenSpout and League CSV

## Why use this ?

Importing/exporting csv data is a very common task in web development. While it's a very efficient format, it's also
somewhat difficult for end users that are used to Excel. This is why you often end up accepting also xlsx format as a import/export target.

Ideally, importing single sheets of csv or excel should be just a matter of changing an adapter. Thankfully, this package does just this :-)

## Supported packages

Native php: very fast csv import/export, but limited features. Can read/output streams.

OpenSpout: fast csv and excel import/export
https://github.com/openspout/openspout

League CSV: very fast csv import/export. Can read streams.
https://github.com/thephpleague/csv

PhpSpreadsheet: slow excel (xls and xlsx) and csv import/export, but more features
https://github.com/PHPOffice/PhpSpreadsheet

SimpleXLSX: very fast excel import/export
https://github.com/shuchkin/simplexlsx
https://github.com/shuchkin/simplexlsxgen

This package will prioritize installed library, by order of performance. You can also pick your preferred default adapter for each format like this:

```php
SpreadCompat::$preferredCsvAdapter = SpreadCompat::NATIVE; // our native csv adapter is the fastest
SpreadCompat::$preferredXlsxAdapter = SpreadCompat::NATIVE; // our native xlsx adapter is the fastest
```

## Using the facade

While you can use individual adapters, it's very likely you don't want to bother too much
how your files are read and written. This package provides a simple facade with static
methods in order to read and write files.

Please note that read methods return a `Generator`. If you want an array, you need to use `iterator_to_array`.

```php
$data = iterator_to_array(SpreadCompat::read('myfile.csv'));

// or
foreach(SpreadCompat::read('myfile.csv') as $row) {
    // Do something
}
```

## Output to browser

This package includes a simple way to leverage output to browser type of functionnality.

Some adapters allow you to stream directly the response.

```php
SpreadCompat::output('myfile.csv');
exit();
```

## Configure

### Using named arguments

This package accepts options using ...opts, this means you can freely use named arguments or pass an array.

```php
$data = iterator_to_array(SpreadCompat::read('myfile.csv', assoc: true));

// or
$data = iterator_to_array(SpreadCompat::read('myfile.csv', ...$opts));
```

### Using options object

You can also use the `Options` class that regroups all available options for all adapters. Unsupported options are ignored.

```php
$options = new Options();
$options->separator = ";";
$data = iterator_to_array(SpreadCompat::read('myfile.csv', $options));
```

## Setting the adapter

Instead of relying on the static variables, you can choose which adapter to use:

```php
$csvData = SpreadCompat::readString($csv, adapter: SpreadCompat::NATIVE);
// or
$options = new Options();
$options->adapter = SpreadCompat::NATIVE;
$csvData = SpreadCompat::readString($csv, $options);
```

## Worksheets

This package supports only 1 worksheet, as it is meant to be able to replace csv by xlsx or vice versa

## Benchmarks

Since we can compare our solutions, there is a built in bench script. You can check the results here

- [read benchmark](docs/bench-read.md)
- [write benchmark](docs/bench-write.md)

For simple imports/exports, it's very clear that using the `Native` adapter is the fastest.

Otherwise, `league/csv` and `shuchkin/simplexlsx` are great choices.

Stop wasting cpu cycles right now and please use the most efficient adapter :-)
