# Spread Compat

> A unified API for fast CSV, XLSX and ODS import/export in PHP

Powered by Baresheet by default, with adapters for PhpSpreadsheet, OpenSpout, Xlswriter, League CSV and SimpleXLSX.

## Why use this ?

Importing/exporting csv data is a very common task in web development. While it's a very efficient format, it's also
somewhat difficult for end users that are used to Excel. This is why you often end up accepting also xlsx or ods format as a import/export target.

Ideally, importing single sheets of csv, excel or ods should be just a matter of changing an adapter. Thankfully, this package does just this :-)

## Supported packages

**Baresheet (recommended/default):** the reference adapter for Spread Compat. Fast, memory-efficient CSV, XLSX and ODS import/export with streaming support and a consistent API across all three formats. It covers the typical single-sheet import/export use cases Spread Compat is designed for.
[https://github.com/lekoala/baresheet](https://github.com/lekoala/baresheet)

Xlswriter: specialized XLSX performance option backed by the C extension. Particularly useful when maximum XLSX write performance is required.
[https://github.com/viest/php-ext-xlswriter](https://github.com/viest/php-ext-xlswriter)

OpenSpout: fast csv, excel (xlsx) and ods import/export. A good interoperability option for projects that already use it.
[https://github.com/openspout/openspout](https://github.com/openspout/openspout)

League CSV: very fast csv import/export. Can read streams. A natural choice for existing League CSV users.
[https://github.com/thephpleague/csv](https://github.com/thephpleague/csv)

PhpSpreadsheet: excel (xls, xlsx) and ods and csv import/export with advanced features such as complex formatting, formulas or workbook manipulation. The only adapter covering legacy `.xls`. If you need those advanced spreadsheet features, PhpSpreadsheet may be a better fit.
[https://github.com/PHPOffice/PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet)

SimpleXLSX: lightweight xlsx import/export. A simple alternative for existing users.
[https://github.com/shuchkin/simplexlsx](https://github.com/shuchkin/simplexlsx)
[https://github.com/shuchkin/simplexlsxgen](https://github.com/shuchkin/simplexlsxgen)

**Baresheet is the reference implementation and the recommended default for CSV, XLSX and ODS.** It provides the best overall fit for Spread Compat's single-sheet import/export use case: fast reads and writes, low memory usage, streaming support, and consistent behavior across formats.

Other adapters remain available for projects that already depend on them or need capabilities outside Baresheet's scope.

```php
// Override the default when you specifically need another adapter
SpreadCompat::$preferredXlsxAdapter = SpreadCompat::XLSWRITER;
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
SpreadCompat::output($data, 'myfile.csv');
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
$options->adapter = SpreadCompat::BARESHEET;
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

For simple imports/exports, it's very clear that using the Baresheet adapter is the fastest overall choice.
