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
        $this->assertEquals(";", $csv->separator);

        // Or use directly
        $csvData = SpreadCompat::read(__DIR__ . '/data/separator.csv', $options);
        $this->assertNotEmpty(iterator_to_array($csvData));
    }

    public function testCanUseNamedArguments()
    {
        $csv = new Native();
        $csv->configure(separator: ";");
        $this->assertEquals(";", $csv->separator);
    }

    public function testCanUseArray()
    {
        $csv = new Native();
        $csv->configure(...["separator" => ";"]);
        $this->assertEquals(";", $csv->separator);
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

        $this->assertEquals($csvData, $csvBomData);
        $this->assertEquals($csvData, $xlsxData);
        $this->assertEquals($csvBomData, $xlsxData);
    }

    public function testCanReadTemp()
    {
        // a file without extension
        $filename = __DIR__ . '/data/basic';
        $csvData = SpreadCompat::read($filename, ['extension' => 'csv']);
        $this->assertNotEmpty(iterator_to_array($csvData));
        $csvData = SpreadCompat::read($filename, extension: 'csv');
        $this->assertNotEmpty(iterator_to_array($csvData));

        $this->expectException(\Exception::class);
        $csvData = SpreadCompat::read($filename);
    }

    public function testCanDetectContentType()
    {
        $csv = file_get_contents(__DIR__ . '/data/basic.csv');
        $this->assertTrue('csv' == SpreadCompat::getExtensionForContent($csv), "Content is: $csv");
        $xlsx = file_get_contents(__DIR__ . '/data/basic.xlsx');
        $this->assertTrue('xlsx' == SpreadCompat::getExtensionForContent($xlsx), "Content is: $xlsx");
    }

    public function testCanSpecifyAdapter()
    {
        // Csv, with extension in opts or as param
        $adapter = SpreadCompat::getAdapterFromOpts([
            'adapter' => SpreadCompat::NATIVE,
            'extension' => 'csv'
        ]);
        $this->assertInstanceOf(\LeKoala\SpreadCompat\Csv\Native::class, $adapter);
        $adapter = SpreadCompat::getAdapterFromOpts([
            'adapter' => SpreadCompat::PHP_SPREADSHEET,
            'extension' => 'csv'
        ]);
        $this->assertInstanceOf(\LeKoala\SpreadCompat\Csv\PhpSpreadsheet::class, $adapter);
        $adapter = SpreadCompat::getAdapterFromOpts([
            'adapter' => SpreadCompat::PHP_SPREADSHEET,
        ], 'csv');
        $this->assertInstanceOf(\LeKoala\SpreadCompat\Csv\PhpSpreadsheet::class, $adapter);
        // Xlsx
        $adapter = SpreadCompat::getAdapterFromOpts([
            'adapter' => SpreadCompat::PHP_SPREADSHEET,
        ], 'xlsx');
        $this->assertInstanceOf(\LeKoala\SpreadCompat\Xlsx\PhpSpreadsheet::class, $adapter);
        $adapter = SpreadCompat::getAdapterFromOpts([
            'adapter' => SpreadCompat::NATIVE,
        ], 'xlsx');
        $this->assertInstanceOf(\LeKoala\SpreadCompat\Xlsx\Native::class, $adapter);
        // Can specify full class
        $adapter = SpreadCompat::getAdapterFromOpts([
            'adapter' => \LeKoala\SpreadCompat\Xlsx\Native::class,
        ], 'xlsx');
        $this->assertInstanceOf(\LeKoala\SpreadCompat\Xlsx\Native::class, $adapter);

        // Make sure it actually works
        $csv = file_get_contents(__DIR__ . '/data/basic.csv');
        $csvData = SpreadCompat::readString($csv, null, adapter: SpreadCompat::NATIVE);
        $this->assertNotEmpty(iterator_to_array($csvData));
        $options = new Options();
        $options->adapter = SpreadCompat::NATIVE;
        $csvData = SpreadCompat::readString($csv, null, $options);
        $this->assertNotEmpty(iterator_to_array($csvData));
    }
}
