<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Tests;

use LeKoala\SpreadCompat\Common\Options;
use LeKoala\SpreadCompat\Csv\Native;
use LeKoala\SpreadCompat\SpreadCompat;
use PHPUnit\Framework\TestCase;

class SpreadCompatCommonTest extends TestCase
{
    public function testCanUseOptions()
    {
        $options = new Options();
        $options->separator = ";";

        // Can use configure
        $csv = new Native();
        $csv->configure($options);
        self::assertEquals(";", $csv->separator);

        // Or use directly
        $csvData = SpreadCompat::read(__DIR__ . '/data/separator.csv', $options);
        self::assertNotEmpty(iterator_to_array($csvData));
    }

    public function testCanUseNamedArguments()
    {
        $csv = new Native();
        $csv->configure(separator: ";");
        self::assertEquals(";", $csv->separator);
    }

    public function testCanUseArray()
    {
        $csv = new Native();
        $csv->configure(...["separator" => ";"]);
        self::assertEquals(";", $csv->separator);
    }

    public function testCanReadContents()
    {
        // Extension is determined based on content
        $csvBom = file_get_contents(__DIR__ . '/data/bom.csv');
        $csvBomData = SpreadCompat::readString($csvBom);
        $csv = file_get_contents(__DIR__ . '/data/basic.csv');
        $csvData = SpreadCompat::readString($csv);
        $xlsx = file_get_contents(__DIR__ . '/data/basic.xlsx');
        $xlsxData = SpreadCompat::readString($xlsx);

        self::assertEquals($csvData, $csvBomData);
        self::assertEquals($csvData, $xlsxData);
        self::assertEquals($csvBomData, $xlsxData);
    }

    public function testCanReadTemp()
    {
        // a file without extension
        $filename = __DIR__ . '/data/basic';
        $csvData = SpreadCompat::read($filename, ['extension' => 'csv']);
        self::assertNotEmpty(iterator_to_array($csvData));
        $csvData = SpreadCompat::read($filename, extension: 'csv');
        self::assertNotEmpty(iterator_to_array($csvData));

        $this->expectException(\Exception::class);
        $csvData = SpreadCompat::read($filename);
    }

    public function testCanDetectContentType()
    {
        $csv = file_get_contents(__DIR__ . '/data/basic.csv');
        self::assertTrue('csv' == SpreadCompat::getExtensionForContent($csv), "Content is: $csv");
        $xlsx = file_get_contents(__DIR__ . '/data/basic.xlsx');
        self::assertTrue('xlsx' == SpreadCompat::getExtensionForContent($xlsx), "Content is: $xlsx");
    }

    public function testCanSpecifyAdapter()
    {
        // Csv, with extension in opts or as param
        $adapter = SpreadCompat::getAdapterFromOpts([
            'adapter' => SpreadCompat::NATIVE,
            'extension' => 'csv'
        ]);
        self::assertInstanceOf(\LeKoala\SpreadCompat\Csv\Native::class, $adapter);
        $adapter = SpreadCompat::getAdapterFromOpts([
            'adapter' => SpreadCompat::PHP_SPREADSHEET,
            'extension' => 'csv'
        ]);
        self::assertInstanceOf(\LeKoala\SpreadCompat\Csv\PhpSpreadsheet::class, $adapter);
        $adapter = SpreadCompat::getAdapterFromOpts([
            'adapter' => SpreadCompat::PHP_SPREADSHEET,
        ], 'csv');
        self::assertInstanceOf(\LeKoala\SpreadCompat\Csv\PhpSpreadsheet::class, $adapter);
        // Xlsx
        $adapter = SpreadCompat::getAdapterFromOpts([
            'adapter' => SpreadCompat::PHP_SPREADSHEET,
        ], 'xlsx');
        self::assertInstanceOf(\LeKoala\SpreadCompat\Xlsx\PhpSpreadsheet::class, $adapter);
        $adapter = SpreadCompat::getAdapterFromOpts([
            'adapter' => SpreadCompat::NATIVE,
        ], 'xlsx');
        self::assertInstanceOf(\LeKoala\SpreadCompat\Xlsx\Native::class, $adapter);
        // Can specify full class
        $adapter = SpreadCompat::getAdapterFromOpts([
            'adapter' => \LeKoala\SpreadCompat\Xlsx\Native::class,
        ], 'xlsx');
        self::assertInstanceOf(\LeKoala\SpreadCompat\Xlsx\Native::class, $adapter);

        // Make sure it actually works
        $csv = file_get_contents(__DIR__ . '/data/basic.csv');
        $csvData = SpreadCompat::readString($csv, null, adapter: SpreadCompat::NATIVE);
        self::assertNotEmpty(iterator_to_array($csvData));
        $options = new Options();
        $options->adapter = SpreadCompat::NATIVE;
        $csvData = SpreadCompat::readString($csv, null, $options);
        self::assertNotEmpty(iterator_to_array($csvData));
    }
}
