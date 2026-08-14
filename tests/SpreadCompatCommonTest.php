<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Tests;

use LeKoala\SpreadCompat\Common\Options;
use LeKoala\SpreadCompat\Csv\Native;
use LeKoala\SpreadCompat\SpreadCompat;
use LeKoala\SpreadCompat\StreamWriterInterface;
use PHPUnit\Framework\Attributes\DataProvider;
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
        $ods = file_get_contents(__DIR__ . '/data/basic.ods');
        self::assertTrue('ods' == SpreadCompat::getExtensionForContent($ods), "Content is: $ods");
    }

    public static function writeStringFormsProvider(): array
    {
        return [
            'csv' => ['csv'],
            'xlsx' => ['xlsx'],
            'ods' => ['ods'],
        ];
    }

    #[DataProvider('writeStringFormsProvider')]
    public function testFacadeCanWriteString(string $ext)
    {
        $data = [
            ['firstname', 'surname', 'email'],
            ['john', 'doe', 'john.doe@example.com'],
        ];

        // All three public forms must produce the same result and round-trip
        $forms = [
            SpreadCompat::writeString($data, $ext),
            SpreadCompat::writeString($data, extension: $ext),
            SpreadCompat::writeString($data, adapter: SpreadCompat::BARESHEET, extension: $ext),
        ];

        foreach ($forms as $string) {
            self::assertNotEmpty($string, "writeString for $ext");
            $decoded = iterator_to_array(SpreadCompat::readString($string, $ext, assoc: true));
            self::assertCount(1, $decoded, "roundtrip for $ext");
            self::assertEquals('john', $decoded[0]['firstname'], "roundtrip for $ext");
        }
    }

    public function testFacadeCanOutput()
    {
        $data = [
            ['firstname', 'surname', 'email'],
            ['john', 'doe', 'john.doe@example.com'],
        ];

        ob_start();
        SpreadCompat::output($data, 'test.csv', adapter: SpreadCompat::BARESHEET);
        $out = ob_get_clean();

        self::assertNotEmpty($out);
        self::assertSame(
            SpreadCompat::writeString($data, 'csv', adapter: SpreadCompat::BARESHEET),
            $out
        );
    }

    public function testConfigureEquivalence()
    {
        $named = new Native();
        $named->configure(separator: ';');
        $array = new Native();
        $array->configure(['separator' => ';']);
        $options = new Native();
        $options->configure(new Options(separator: ';'));

        self::assertEquals(';', $named->separator);
        self::assertEquals($named->separator, $array->separator);
        self::assertEquals($named->separator, $options->separator);

        // Options and named arguments can be mixed: both must be applied
        $mixed = new Native();
        $mixed->configure(new Options(separator: ';'), bom: false);
        self::assertEquals(';', $mixed->separator);
        self::assertFalse($mixed->bom);

        // Same for arrays
        $mixed = new Native();
        $mixed->configure(['separator' => ';'], bom: false);
        self::assertEquals(';', $mixed->separator);
        self::assertFalse($mixed->bom);
    }

    public function testConfigurePrecedenceLastWins()
    {
        $csv = new Native();
        $csv->configure(new Options(separator: ','), separator: ';');
        self::assertEquals(';', $csv->separator);

        $csv = new Native();
        $csv->configure(['separator' => ','], separator: ';');
        self::assertEquals(';', $csv->separator);

        $csv = new Native();
        $csv->configure(new Options(separator: ';'), separator: ',');
        self::assertEquals(',', $csv->separator);
    }

    public function testWriteStringWithOptionsExtension()
    {
        $data = [
            ['firstname', 'surname', 'email'],
            ['john', 'doe', 'john.doe@example.com'],
        ];

        $opts = new Options(extension: 'xlsx');
        $string = SpreadCompat::writeString($data, null, $opts);
        self::assertNotEmpty($string);
        self::assertStringStartsWith('PK', $string, "Options extension must select the xlsx adapter");

        $decoded = iterator_to_array(SpreadCompat::readString($string, 'xlsx', assoc: true));
        self::assertEquals('john', $decoded[0]['firstname']);
    }

    public function testAdapterSelectionNamedArguments()
    {
        $csv = file_get_contents(__DIR__ . '/data/basic.csv');
        $data = iterator_to_array(SpreadCompat::readString($csv, adapter: SpreadCompat::BARESHEET));
        self::assertNotEmpty($data);

        // adapter + extension is the structurant pair of the facade
        $xlsx = file_get_contents(__DIR__ . '/data/basic.xlsx');
        $data = iterator_to_array(SpreadCompat::readString($xlsx, adapter: SpreadCompat::BARESHEET, extension: 'xlsx'));
        self::assertCount(1, $data);
        self::assertCount(3, $data[0]);
    }

    public function testGetAdapterNameThrowsOnUnsupportedExtension()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No adapter found for wat');
        SpreadCompat::getAdapterName('wat');
    }

    public function testGetAdapterByNameThrowsOnUnknownAdapter()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid adapter');
        SpreadCompat::getAdapterByName('xlsx', 'Wat');
    }

    public function testGetAdapterByNameThrowsWhenDependencyUnavailable()
    {
        if (extension_loaded('xlswriter')) {
            self::markTestSkipped('xlswriter extension is available');
        }

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Adapter Xlswriter is not available for xlsx');
        SpreadCompat::getAdapterByName('xlsx', SpreadCompat::XLSWRITER);
    }

    public function testWriteStringThrowsWithoutAdapterOrExtension()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No adapter or extension specified for string');
        SpreadCompat::writeString([
            ['firstname', 'surname', 'email'],
            ['john', 'doe', 'john.doe@example.com'],
        ]);
    }

    public function testNativeIsBaresheetAlias()
    {
        self::assertInstanceOf(\LeKoala\SpreadCompat\Csv\Baresheet::class, new \LeKoala\SpreadCompat\Csv\Native());
        self::assertInstanceOf(\LeKoala\SpreadCompat\Xlsx\Baresheet::class, new \LeKoala\SpreadCompat\Xlsx\Native());
        self::assertInstanceOf(\LeKoala\SpreadCompat\Ods\Baresheet::class, new \LeKoala\SpreadCompat\Ods\Native());
    }

    public static function baresheetExtensionsProvider(): array
    {
        return [
            'xlsx' => ['xlsx'],
            'ods' => ['ods'],
        ];
    }

    #[DataProvider('baresheetExtensionsProvider')]
    public function testBaresheetValuesPassThrough(string $ext)
    {
        $data = [
            ['name' => 'null', 'value' => null],
            ['name' => 'string', 'value' => '123'],
            ['name' => 'int', 'value' => 123],
            ['name' => 'float', 'value' => 12.5],
            ['name' => 'bool', 'value' => true],
            ['name' => 'date', 'value' => '2024-01-15'],
            ['name' => 'datetime', 'value' => '2024-01-15 10:30:00'],
            ['name' => 'time', 'value' => '10:30:00'],
        ];
        $string = SpreadCompat::writeString($data, $ext, adapter: SpreadCompat::BARESHEET);
        self::assertNotEmpty($string);

        $facade = iterator_to_array(
            SpreadCompat::readString($string, $ext, adapter: SpreadCompat::BARESHEET, assoc: true)
        );
        $raw = iterator_to_array($this->rawReader($ext)->readString($string));

        self::assertSame($raw, $facade, "SpreadCompat must not alter Baresheet values for $ext");
    }

    private function rawReader(string $ext): \LeKoala\Baresheet\ReaderInterface
    {
        $reader = match ($ext) {
            'xlsx' => new \LeKoala\Baresheet\XlsxReader(),
            'ods' => new \LeKoala\Baresheet\OdsReader(),
            default => throw new \InvalidArgumentException("Unsupported extension $ext"),
        };
        $reader->assoc = true;
        return $reader;
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

    public function testGetLetter()
    {
        self::assertEquals('A', SpreadCompat::getLetter(1));
        self::assertEquals('Z', SpreadCompat::getLetter(26));
        self::assertEquals('AA', SpreadCompat::getLetter(27));
        self::assertEquals('ZZ', SpreadCompat::getLetter(702));
        self::assertEquals('AAA', SpreadCompat::getLetter(703));
        self::assertEquals('XFD', SpreadCompat::getLetter(16384));
    }

    public function testExcelCell()
    {
        self::assertEquals('A1', SpreadCompat::excelCell(0, 0));
        self::assertEquals('$A$1', SpreadCompat::excelCell(0, 0, true));
        self::assertEquals('Z10', SpreadCompat::excelCell(9, 25));
        self::assertEquals('$Z$10', SpreadCompat::excelCell(9, 25, true));
        self::assertEquals('AA100', SpreadCompat::excelCell(99, 26));
        self::assertEquals('XFD16384', SpreadCompat::excelCell(16383, 16383));
    }

    public function testDefaultAdaptersAreBaresheet(): void
    {
        $originalCsv = SpreadCompat::$preferredCsvAdapter;
        $originalXlsx = SpreadCompat::$preferredXlsxAdapter;
        $originalXslx = SpreadCompat::$preferredXslxAdapter;
        $originalOds = SpreadCompat::$preferredOdsAdapter;

        try {
            SpreadCompat::$preferredCsvAdapter = null;
            SpreadCompat::$preferredXlsxAdapter = null;
            SpreadCompat::$preferredXslxAdapter = null;
            SpreadCompat::$preferredOdsAdapter = null;

            self::assertSame(SpreadCompat::BARESHEET, SpreadCompat::getAdapterName('csv'));
            self::assertSame(SpreadCompat::BARESHEET, SpreadCompat::getAdapterName('xlsx'));
            self::assertSame(SpreadCompat::BARESHEET, SpreadCompat::getAdapterName('ods'));

            self::assertInstanceOf(
                \LeKoala\SpreadCompat\Csv\Baresheet::class,
                SpreadCompat::getAdapter('csv')
            );
            self::assertInstanceOf(
                \LeKoala\SpreadCompat\Xlsx\Baresheet::class,
                SpreadCompat::getAdapter('xlsx')
            );
            self::assertInstanceOf(
                \LeKoala\SpreadCompat\Ods\Baresheet::class,
                SpreadCompat::getAdapter('ods')
            );
        } finally {
            SpreadCompat::$preferredCsvAdapter = $originalCsv;
            SpreadCompat::$preferredXlsxAdapter = $originalXlsx;
            SpreadCompat::$preferredXslxAdapter = $originalXslx;
            SpreadCompat::$preferredOdsAdapter = $originalOds;
        }
    }

    public function testUnavailablePreferredAdapterFallsBackToBaresheet(): void
    {
        $originalCsv = SpreadCompat::$preferredCsvAdapter;
        $originalXlsx = SpreadCompat::$preferredXlsxAdapter;
        $originalXslx = SpreadCompat::$preferredXslxAdapter;
        $originalOds = SpreadCompat::$preferredOdsAdapter;

        try {
            SpreadCompat::$preferredCsvAdapter = 'NonExistent';
            SpreadCompat::$preferredXlsxAdapter = 'NonExistent';
            SpreadCompat::$preferredXslxAdapter = null;
            SpreadCompat::$preferredOdsAdapter = 'NonExistent';

            self::assertSame(SpreadCompat::BARESHEET, SpreadCompat::getAdapterName('csv'));
            self::assertSame(SpreadCompat::BARESHEET, SpreadCompat::getAdapterName('xlsx'));
            self::assertSame(SpreadCompat::BARESHEET, SpreadCompat::getAdapterName('ods'));

            // An available adapter is still honored
            SpreadCompat::$preferredXlsxAdapter = SpreadCompat::NATIVE;
            self::assertSame(SpreadCompat::NATIVE, SpreadCompat::getAdapterName('xlsx'));
        } finally {
            SpreadCompat::$preferredCsvAdapter = $originalCsv;
            SpreadCompat::$preferredXlsxAdapter = $originalXlsx;
            SpreadCompat::$preferredXslxAdapter = $originalXslx;
            SpreadCompat::$preferredOdsAdapter = $originalOds;
        }
    }

    public function testPreferredXlsxAdapter()
    {
        $originalXlsx = SpreadCompat::$preferredXlsxAdapter;
        $originalXslx = SpreadCompat::$preferredXslxAdapter;

        try {
            SpreadCompat::$preferredXlsxAdapter = null;
            SpreadCompat::$preferredXslxAdapter = null;

            // Correctly spelled property is used when set
            SpreadCompat::$preferredXlsxAdapter = SpreadCompat::NATIVE;
            self::assertEquals(SpreadCompat::NATIVE, SpreadCompat::getAdapterName('xlsx'));

            // Legacy typo'd property still works for backward compatibility
            SpreadCompat::$preferredXlsxAdapter = null;
            SpreadCompat::$preferredXslxAdapter = SpreadCompat::OPEN_SPOUT;
            self::assertEquals(SpreadCompat::OPEN_SPOUT, SpreadCompat::getAdapterName('xlsx'));

            // Correctly spelled property takes precedence over the legacy one
            SpreadCompat::$preferredXlsxAdapter = SpreadCompat::NATIVE;
            SpreadCompat::$preferredXslxAdapter = SpreadCompat::OPEN_SPOUT;
            self::assertEquals(SpreadCompat::NATIVE, SpreadCompat::getAdapterName('xlsx'));
        } finally {
            SpreadCompat::$preferredXlsxAdapter = $originalXlsx;
            SpreadCompat::$preferredXslxAdapter = $originalXslx;
        }
    }

    public function testOptionsSupportsExtension()
    {
        $options = new Options(extension: 'xlsx', adapter: SpreadCompat::BARESHEET);
        self::assertEquals('xlsx', $options->extension);
        self::assertEquals(SpreadCompat::BARESHEET, $options->adapter);

        $xlsx = file_get_contents(__DIR__ . '/data/basic.xlsx');
        $data = iterator_to_array(SpreadCompat::readString($xlsx, null, $options));
        self::assertNotEmpty($data);
    }

    public function testOptionsNormalize()
    {
        $options = new Options(adapter: SpreadCompat::BARESHEET, extension: 'csv');
        $normalized = Options::normalize([$options, ['assoc' => true]]);
        self::assertSame(SpreadCompat::BARESHEET, $normalized['adapter']);
        self::assertSame('csv', $normalized['extension']);
        self::assertTrue($normalized['assoc']);

        // Named arguments survive normalization
        $normalized = Options::normalize(['extension' => 'ods']);
        self::assertSame('ods', $normalized['extension']);
    }

    public function testOptionsRejectsUnknownKeys()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Options(seperator: ';');
    }

    public function testCanMixOptionsAndNamedArguments()
    {
        $csv = file_get_contents(__DIR__ . '/data/headers.csv');
        $data = iterator_to_array(
            SpreadCompat::readString($csv, null, new Options(adapter: SpreadCompat::BARESHEET), assoc: true)
        );
        self::assertNotEmpty($data);
        self::assertEquals('john', $data[0]['firstname']);
    }

    public function testBaresheetAdaptersImplementStreamWriter()
    {
        self::assertInstanceOf(StreamWriterInterface::class, new \LeKoala\SpreadCompat\Csv\Baresheet());
        self::assertInstanceOf(StreamWriterInterface::class, new \LeKoala\SpreadCompat\Xlsx\Baresheet());
        self::assertInstanceOf(StreamWriterInterface::class, new \LeKoala\SpreadCompat\Ods\Baresheet());
        // Native aliases inherit the capability
        self::assertInstanceOf(StreamWriterInterface::class, new \LeKoala\SpreadCompat\Csv\Native());
        self::assertInstanceOf(StreamWriterInterface::class, new \LeKoala\SpreadCompat\Xlsx\Native());
    }

    public static function writeStreamProvider(): array
    {
        return [
            'csv' => ['csv'],
            'xlsx' => ['xlsx'],
            'ods' => ['ods'],
        ];
    }

    #[DataProvider('writeStreamProvider')]
    public function testFacadeWriteStream(string $ext)
    {
        $data = [
            ['firstname', 'surname', 'email'],
            ['john', 'doe', 'john.doe@example.com'],
        ];

        $stream = SpreadCompat::writeStream($data, $ext);
        self::assertIsResource($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);

        self::assertNotEmpty($contents);
        self::assertSame(
            SpreadCompat::writeString($data, $ext),
            $contents,
            "writeStream content must match writeString for $ext"
        );

        // It round-trips
        $decoded = iterator_to_array(SpreadCompat::readString($contents, $ext, assoc: true));
        self::assertEquals('john', $decoded[0]['firstname']);
    }

    public function testWriteStreamThrowsForNonStreamAdapter()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('does not support stream writing');
        SpreadCompat::writeStream([
            ['a', 'b'],
        ], 'xlsx', adapter: SpreadCompat::PHP_SPREADSHEET);
    }

    public function testWriteStreamThrowsWithoutAdapterOrExtension()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No adapter or extension specified for stream');
        SpreadCompat::writeStream([
            ['a', 'b'],
        ]);
    }

    public function testOptionsConfigureRejectsUnknownKeys()
    {
        $options = new Options();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('seperator');
        $options->configure(seperator: ';');
    }

    public function testPreferredAdapterInvalidForFormatFallsBackToBaresheet()
    {
        $originalOds = SpreadCompat::$preferredOdsAdapter;
        $originalCsv = SpreadCompat::$preferredCsvAdapter;

        try {
            // League is installed but there is no Ods\League adapter
            SpreadCompat::$preferredOdsAdapter = SpreadCompat::LEAGUE;
            self::assertSame(SpreadCompat::BARESHEET, SpreadCompat::getAdapterName('ods'));

            // SimpleXLSX is installed but there is no Csv\Simple adapter
            SpreadCompat::$preferredCsvAdapter = SpreadCompat::SIMPLE;
            self::assertSame(SpreadCompat::BARESHEET, SpreadCompat::getAdapterName('csv'));
        } finally {
            SpreadCompat::$preferredOdsAdapter = $originalOds;
            SpreadCompat::$preferredCsvAdapter = $originalCsv;
        }
    }

    public static function outputStreamProvider(): array
    {
        return [
            'csv' => ['csv'],
            'xlsx' => ['xlsx'],
            'ods' => ['ods'],
        ];
    }

    #[DataProvider('outputStreamProvider')]
    public function testFacadeOutputStreams(string $ext)
    {
        $data = [
            ['firstname', 'surname', 'email'],
            ['john', 'doe', 'john.doe@example.com'],
        ];

        ob_start();
        SpreadCompat::output($data, "test.$ext", adapter: SpreadCompat::BARESHEET, stream: true);
        $output = ob_get_clean();

        self::assertNotSame('', $output);
        $rows = iterator_to_array(SpreadCompat::readString($output, $ext, adapter: SpreadCompat::BARESHEET));
        self::assertCount(2, $rows, "output stream for $ext must produce a readable document");
        self::assertEquals('john', $rows[1][0]);
    }

    public static function outputBufferedProvider(): array
    {
        return [
            'xlsx' => ['xlsx'],
            'ods' => ['ods'],
        ];
    }

    #[DataProvider('outputBufferedProvider')]
    public function testFacadeOutputBuffered(string $ext)
    {
        $data = [
            ['firstname', 'surname', 'email'],
            ['john', 'doe', 'john.doe@example.com'],
        ];

        ob_start();
        SpreadCompat::output($data, "test.$ext", adapter: SpreadCompat::BARESHEET, stream: false);
        $output = ob_get_clean();

        self::assertNotSame('', $output);

        // Zip-based formats are compared functionally, not byte-identically
        $expected = SpreadCompat::writeString($data, $ext, adapter: SpreadCompat::BARESHEET);
        self::assertSame(
            iterator_to_array(SpreadCompat::readString($expected, $ext, adapter: SpreadCompat::BARESHEET)),
            iterator_to_array(SpreadCompat::readString($output, $ext, adapter: SpreadCompat::BARESHEET)),
        );
    }
}
