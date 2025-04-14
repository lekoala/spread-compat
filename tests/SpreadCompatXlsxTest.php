<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use LeKoala\SpreadCompat\Xlsx\Simple;
use LeKoala\SpreadCompat\SpreadCompat;
use LeKoala\SpreadCompat\Xlsx\Native;
use LeKoala\SpreadCompat\Xlsx\OpenSpout;
use LeKoala\SpreadCompat\Xlsx\PhpSpreadsheet;

class SpreadCompatXlsxTest extends TestCase
{
    public function testFacadeCanReadXlsx()
    {
        $data = iterator_to_array(SpreadCompat::read(__DIR__ . '/data/basic.xlsx'));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);
    }

    public function testOpenSpoutCanReadXlsx()
    {
        $openSpout = new OpenSpout();
        $data = iterator_to_array($openSpout->readFile(__DIR__ . '/data/empty.xlsx'));
        $this->assertCount(0, $data, "Data is : " . json_encode($data));

        $openSpout = new OpenSpout();
        $data = iterator_to_array($openSpout->readFile(__DIR__ . '/data/basic.xlsx'));
        $this->assertCount(1, $data, "Data is : " . json_encode($data));
        $this->assertCount(3, $data[0]);

        $openSpout = new OpenSpout();
        $data = iterator_to_array($openSpout->readFile(__DIR__ . '/data/header.xlsx', assoc: true));
        $this->assertCount(1, $data, "Data is : " . json_encode($data));
        $this->assertCount(4, $data[0]);
    }

    public function testOpenSpoutCanWriteXlsx()
    {
        $openSpout = new OpenSpout();
        $string = $openSpout->writeString([
            [
                "john",
                "doe",
                "john.doe@example.com"
            ]
        ]);
        $this->assertStringContainsString('[Content_Types].xml', $string);

        $openSpout = new OpenSpout();
        $string2 = $openSpout->writeString([
            [
                "firstname",
                "surname",
                "email"
            ],
            [
                "john",
                "doe",
                "john.doe@example.com"
            ]
        ], ...[
            'autofilter' => 'A1:C1',
            'freezePane' => 'A1',
        ]);
        $this->assertStringContainsString('[Content_Types].xml', $string);
        $this->assertNotEquals($string, $string2);

        $coordsOpenSpout = new OpenSpout();
        $coordsOpenSpout->autofilter = 'A1:C1';
        $this->assertEquals([0, 1, 2, 1], $coordsOpenSpout->autofilterCoords());

        $openSpout = new OpenSpout();
        $openSpout->creator = "test";
        $string = $openSpout->writeString([
            [
                "john",
                "doe",
                "john.doe@example.com"
            ]
        ]);
        $this->assertStringContainsString('[Content_Types].xml', $string);
        $tmpFile = SpreadCompat::stringToTempFile($string);
        $props = SpreadCompat::excelProperties($tmpFile);

        // this does not seem to work with older open spout version but it's fairly minor
        $result = PHP_VERSION_ID > 80199 ? "test" : "";
        $this->assertEquals($result, $props['creator']);
        $this->assertNotEquals("OpenSpout", $props['creator']);
    }

    public function testSpreadsheetCanReadXlsx()
    {
        $PhpSpreadsheet = new PhpSpreadsheet();
        $data = iterator_to_array($PhpSpreadsheet->readFile(__DIR__ . '/data/empty.xlsx'));
        $this->assertCount(0, $data);

        $PhpSpreadsheet = new PhpSpreadsheet();
        $data = iterator_to_array($PhpSpreadsheet->readFile(__DIR__ . '/data/basic.xlsx'));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);

        $PhpSpreadsheet = new PhpSpreadsheet();
        $data = iterator_to_array($PhpSpreadsheet->readFile(__DIR__ . '/data/header.xlsx', assoc: true));
        $this->assertCount(1, $data);
        $this->assertCount(4, $data[0]);
    }

    public function testSpreadsheetCanWriteXlsx()
    {
        $openSpout = new PhpSpreadsheet();
        $string = $openSpout->writeString([
            [
                "john",
                "doe",
                "john.doe@example.com"
            ]
        ]);
        $this->assertStringContainsString('[Content_Types].xml', $string);

        $openSpout = new PhpSpreadsheet();
        $string2 = $openSpout->writeString([
            [
                "fname",
                "sname",
                "email"
            ],
            [
                "john",
                "doe",
                "john.doe@example.com"
            ]
        ], ...[
            'autofilter' => 'A1:C1',
            'freezePane' => 'A1',
        ]);
        $this->assertStringContainsString('[Content_Types].xml', $string);
        $this->assertNotEquals($string, $string2);
    }

    public function testSimpleCanReadXlsx()
    {
        $Simple = new Simple();
        $data = iterator_to_array($Simple->readFile(__DIR__ . '/data/empty.xlsx'));
        $this->assertCount(0, $data);

        $Simple = new Simple();
        $data = iterator_to_array($Simple->readFile(__DIR__ . '/data/basic.xlsx'));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);

        $Simple = new Simple();
        $data = iterator_to_array($Simple->readFile(__DIR__ . '/data/header.xlsx', assoc: true));
        $this->assertCount(1, $data);
        $this->assertCount(4, $data[0]);
    }

    public function testSimpleCanWriteXlsx()
    {
        $openSpout = new Simple();
        $string = $openSpout->writeString([
            [
                "john",
                "doe",
                "john.doe@example.com"
            ]
        ]);
        $this->assertStringContainsString('[Content_Types].xml', $string);

        $openSpout = new Simple();
        $string2 = $openSpout->writeString([
            [
                "fname",
                "sname",
                "email"
            ],
            [
                "john",
                "doe",
                "john.doe@example.com"
            ]
        ], ...[
            'autofilter' => 'A1:C1',
            'freezePane' => 'A1',
        ]);
        $this->assertStringContainsString('[Content_Types].xml', $string);
        $this->assertNotEquals($string, $string2);
    }

    public function testNativeCanReadXlsx()
    {
        $Native = new Native();
        $data = iterator_to_array($Native->readFile(__DIR__ . '/data/empty.xlsx'));
        $this->assertCount(0, $data);

        $Native = new Native();
        $data = iterator_to_array($Native->readFile(__DIR__ . '/data/basic.xlsx'));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);

        $Native = new Native();
        $data = iterator_to_array($Native->readFile(__DIR__ . '/data/header.xlsx', assoc: true));
        $this->assertCount(1, $data);
        $this->assertCount(4, $data[0]);
    }

    public function testNativeCanWriteXlsx()
    {
        $Native = new Native();
        $string = $Native->writeString([
            [
                "john",
                "doe",
                "john.doe@example.com"
            ]
        ]);
        $this->assertStringContainsString('[Content_Types].xml', $string);

        $Native = new Native();
        $string2 = $Native->writeString([
            [
                "fname",
                "sname",
                "email"
            ],
            [
                "john",
                "doe",
                "john.doe@example.com"
            ]
        ], ...[
            'autofilter' => 'A1:C1',
            'freezePane' => 'A1',
        ]);
        $this->assertStringContainsString('[Content_Types].xml', $string);
        $this->assertNotEquals($string, $string2);

        // The same, but with stream
        $Native = new Native();
        $Native->stream = true;
        $string = $Native->writeString([
            [
                "john",
                "doe",
                "john.doe@example.com"
            ]
        ]);
        $this->assertStringContainsString('[Content_Types].xml', $string);

        $Native = new Native();
        $string2 = $Native->writeString([
            [
                "fname",
                "sname",
                "email"
            ],
            [
                "john",
                "doe",
                "john.doe@example.com"
            ]
        ], ...[
            'autofilter' => 'A1:C1',
            'freezePane' => 'A1',
            'strea=' => true,
        ]);
        $this->assertStringContainsString('[Content_Types].xml', $string);
        $this->assertNotEquals($string, $string2);
    }

    public function testNativeDontSkipEmptyCols()
    {
        $Native = new Native();
        $Native->assoc = true;
        $data = $Native->readFile(__DIR__ . '/data/empty-col.xlsx');

        $arr = iterator_to_array($data);
        $this->assertEquals([
            [
                'col1' => "v1",
                'col2' => "v2",
                'col3' => null,
                'col4' => "v4",
            ],
            [
                'col1' => "v1",
                'col2' => null,
                'col3' => null,
                'col4' => "v4",
            ],
            [
                'col1' => null,
                'col2' => "v2",
                'col3' => "v3",
                'col4' => null,
            ]
        ], $arr);

        $Native = new Native();
        $Native->assoc = true;
        $data = $Native->readFile(__DIR__ . '/data/empty-col-2.xlsx');

        $arr = iterator_to_array($data);
        $this->assertEquals([
            [
                'col1' => "v1",
                'col2' => "v2",
                'col3' => null,
                'col4' => "v4",
                'col5' => null,
                'col6' => null,
                'col7' => null,
            ],
            [
                'col1' => "v1",
                'col2' => null,
                'col3' => null,
                'col4' => "v4",
                'col5' => null,
                'col6' => null,
                'col7' => null,
            ],
            [
                'col1' => null,
                'col2' => "v2",
                'col3' => "v3",
                'col4' => null,
                'col5' => null,
                'col6' => null,
                'col7' => null,
            ]
        ], $arr);

        $Native = new Native();
        $data = $Native->readFile(__DIR__ . '/data/empty-col-2.xlsx');

        $arr = iterator_to_array($data);
        $this->assertEquals([
            [
                'col1',
                'col2',
                'col3',
                'col4',
                'col5',
                'col6',
                'col7',
            ],
            [
                "v1",
                "v2",
                null,
                "v4",
                null,
                null,
                null,
            ],
            [
                "v1",
                null,
                null,
                "v4",
                null,
                null,
                null,
            ],
            [
                null,
                "v2",
                "v3",
                null,
                null,
                null,
                null,
            ]
        ], $arr);
    }

    public function testNativeThrowProperExceptions()
    {
        $Native = new Native();
        $Native->assoc = true;

        $filename = __DIR__ . '/data/invalid-file.xlsx';
        $this->expectException(Exception::class);
        $data = $Native->readFile($filename);
        $arr = iterator_to_array($data); // triggers reading file
    }

    public function testNativeDates()
    {
        $Native = new Native();
        $Native->assoc = true;
        $data = $Native->readFile(__DIR__ . '/data/date.xlsx');

        $arr = iterator_to_array($data);

        $firstRow = $arr[0];
        $this->assertEquals('2016-10-14', $firstRow['BirthDate']);
        $this->assertEquals('2025-01-01 10:00:00', $firstRow['Created']);
        $this->assertEquals('10:00:00', $firstRow['BestTime']);

        // Test that it works even for silly dates
        $this->assertEquals('1545-01-15', $arr[1]['BirthDate']);
        $this->assertEquals('2955-12-10', $arr[2]['BirthDate']);
        $this->assertEquals('1242-09-16', $arr[3]['BirthDate']);
        $this->assertEquals('1742-09-16', $arr[4]['BirthDate']);
        $this->assertEquals('1900-09-16', $arr[5]['BirthDate']);
        $this->assertEquals('1899-09-16', $arr[6]['BirthDate']);
        $this->assertEquals('4111-09-16', $arr[7]['BirthDate']);
        $this->assertTrue('' === $arr[8]['BirthDate']); // it has no t attribute so it is simply a string

        // Invalid dates are treated as strings
        $this->assertEquals('00/00/0000', $arr[9]['BirthDate']);

        foreach (range(1, 31) as $i) {
            $r = $arr[9 + $i];
            if ($r['Surname'] === 'buggy') {
                continue;
            }
            $this->assertEquals('1899-09-16', $r['BirthDate'], "Failed format $i: " . json_encode($r));
        }
    }

    public function testNativeDurations()
    {
        $Native = new Native();
        $Native->assoc = true;
        $data = $Native->readFile(__DIR__ . '/data/duration.xlsx');

        $arr = iterator_to_array($data);

        // Even when starting with empty columns, it should return
        $this->assertCount(3, $arr);
    }
}
