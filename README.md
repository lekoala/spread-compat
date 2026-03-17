# Spread Compat

> Easily manipulate PhpSpreadsheet, OpenSpout, League CSV and Baresheet

## Why use this ?

Importing/exporting csv data is a very common task in web development. While it's a very efficient format, it's also
somewhat difficult for end users that are used to Excel. This is why you often end up accepting also xlsx or ods format as a import/export target.

Ideally, importing single sheets of csv, excel or ods should be just a matter of changing an adapter. Thankfully, this package does just this :-)

## Supported packages

Baresheet (Native): very fast csv, xlsx and ods import/export, but limited features. Can read/output streams. It is used as our default native adapter.
[https://github.com/lekoala/baresheet](https://github.com/lekoala/baresheet)

OpenSpout: fast csv, excel (xlsx) and ods import/export
[https://github.com/openspout/openspout](https://github.com/openspout/openspout)

League CSV: very fast csv import/export. Can read streams.
[https://github.com/thephpleague/csv](https://github.com/thephpleague/csv)

PhpSpreadsheet: slow excel (xls, xlsx) and ods and csv import/export, but more features
[https://github.com/PHPOffice/PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet)

SimpleXLSX: very fast excel import/export
[https://github.com/shuchkin/simplexlsx](https://github.com/shuchkin/simplexlsx)
[https://github.com/shuchkin/simplexlsxgen](https://github.com/shuchkin/simplexlsxgen)

This package will prioritize installed library, by order of performance. You can also pick your preferred default adapter for each format like this:

```php
SpreadCompat::$preferredCsvAdapter = SpreadCompat::BARESHEET; // or SpreadCompat::NATIVE
SpreadCompat::$preferredXlsxAdapter = SpreadCompat::BARESHEET;
SpreadCompat::$preferredOdsAdapter = SpreadCompat::BARESHEET;
```

## Using the facade

While you can use individual adapters, it's very likely you don't want to bother too much
how your files are read and written. This package provides a simple facade with static
methods in order to read and write files.

Please note that read methods return a `Generator`. If you want an array, you need to use `iterator_to_array`.

```php
$data = iterator_to_array(SpreadCompat::read('myfile.csv'));

// or
foreach(SpreadCompat::read('myfile.xlsx') as $row) {
    // Do something
}

// or even
foreach(SpreadCompat::read('myfile.ods') as $row) {
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
$csvData = SpreadCompat::readString($csv, adapter: SpreadCompat::BARESHEET);
// or
$options = new Options();
$options->adapter = SpreadCompat::NATIVE;
$csvData = SpreadCompat::readString($csv, $options);
```

## Security

### CSV Formula Injection

When exporting to CSV, cell values starting with `=`, `+`, `-`, `@`, `\t`, or `\r` can be interpreted as formulas by spreadsheet software like Excel. This is known as [CSV Formula Injection](https://owasp.org/www-community/attacks/CSV_Injection).

By default, this library does NOT escape these characters to ensure that the data is not altered and remains compatible with other tools that may expect raw data.

If you are generating CSV files for end users to open in Excel and want to protect them from potential formula injection, you should enable the `escapeFormulas` option:

```php
SpreadCompat::write('myfile.csv', $data, escapeFormulas: true);
```

This will prepend a single quote (`'`) to any cell value that could be interpreted as a formula.

## Worksheets

This package supports only 1 worksheet, as it is meant to be able to replace csv by xlsx or vice versa

## Benchmarks

Since we can compare our solutions, there is a built in bench script. You can check the results here

- [read benchmark](docs/bench-read.md)
- [write benchmark](docs/bench-write.md)

For simple imports/exports, it's very clear that using the `Native` (Baresheet) adapter is the fastest overall choice.

Stop wasting cpu cycles right now and please use the most efficient adapter :-)
