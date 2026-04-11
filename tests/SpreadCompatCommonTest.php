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

    public function testCanGetStreamContents()
    {
        $stream = fopen('php://temp', 'r+');
        if (!$stream) {
            throw new \RuntimeException("Failed to open stream");
        }
        $content = "Hello world";
        fwrite($stream, $content);

        $result = SpreadCompat::getStreamContents($stream);
        self::assertEquals($content, $result);

        // Test with empty stream
        $emptyStream = fopen('php://temp', 'r+');
        if (!$emptyStream) {
            throw new \RuntimeException("Failed to open empty stream");
        }
        $result = SpreadCompat::getStreamContents($emptyStream);
        self::assertEquals("", $result);

        fclose($stream);
        fclose($emptyStream);
    }

    public function testEnsureExtension()
    {
        // Already has extension
        self::assertEquals('test.csv', SpreadCompat::ensureExtension('test.csv', 'csv'));
        // Missing extension
        self::assertEquals('test.csv', SpreadCompat::ensureExtension('test', 'csv'));
        // Different extension
        self::assertEquals('test.xlsx.csv', SpreadCompat::ensureExtension('test.xlsx', 'csv'));
        // Case sensitivity (currently fails, but should pass after fix)
        self::assertEquals('test.CSV', SpreadCompat::ensureExtension('test.CSV', 'csv'));
        // Path with extension
        self::assertEquals('/path/to/test.csv', SpreadCompat::ensureExtension('/path/to/test.csv', 'csv'));
        // Path without extension
        self::assertEquals('/path/to/test.csv', SpreadCompat::ensureExtension('/path/to/test', 'csv'));
    }

    public function testGetOutputStream()
    {
        // Test with php://temp
        $stream = SpreadCompat::getOutputStream('php://temp');
        self::assertIsResource($stream);
        self::assertEquals('stream', get_resource_type($stream));
        fclose($stream);

        // Test with a temp file
        $tempFile = SpreadCompat::getTempFilename();
        $stream = SpreadCompat::getOutputStream($tempFile);
        self::assertIsResource($stream);
        self::assertEquals('stream', get_resource_type($stream));
        fclose($stream);
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }

    public function testGetOutputStreamFailure()
    {
        $this->expectException(\RuntimeException::class);
        // Opening a directory for writing should fail
        SpreadCompat::getOutputStream(__DIR__);
    }

    public function testGetInputStream()
    {
        $tempFile = SpreadCompat::getTempFilename();
        file_put_contents($tempFile, 'test');
        $stream = SpreadCompat::getInputStream($tempFile);
        self::assertIsResource($stream);
        self::assertEquals('stream', get_resource_type($stream));
        fclose($stream);
        unlink($tempFile);
    }

    public function testGetInputStreamFailure()
    {
        $this->expectException(\RuntimeException::class);
        // Opening a non-existent file for reading should fail
        SpreadCompat::getInputStream('/non/existent/file');
    }

    public function testIsTempFile()
    {
        self::assertTrue(SpreadCompat::isTempFile('/tmp/S_Cabcdef.tmp'));
        self::assertTrue(SpreadCompat::isTempFile('S_Cabcdef.tmp'));
        self::assertFalse(SpreadCompat::isTempFile('/tmp/not_a_temp_file.tmp'));
        self::assertFalse(SpreadCompat::isTempFile('not_a_temp_file.tmp'));
        self::assertFalse(SpreadCompat::isTempFile('/S_C_dir/file.txt'));

        $temp = SpreadCompat::getTempFilename();
        self::assertTrue(SpreadCompat::isTempFile($temp));
        unlink($temp);
    }
}
