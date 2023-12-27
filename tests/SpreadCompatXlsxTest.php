<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Tests;

use PHPUnit\Framework\TestCase;
use LeKoala\SpreadCompat\Xlsx\Simple;
use LeKoala\SpreadCompat\SpreadCompat;
use LeKoala\SpreadCompat\Xlsx\OpenSpout;
use LeKoala\SpreadCompat\Xlsx\PhpSpreadsheet;

class SpreadCompatXlsxTest extends TestCase
{
    public function testFacadeCanReadXksx()
    {
        $data = iterator_to_array(SpreadCompat::read(__DIR__ . '/data/basic.xlsx'));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);
    }

    public function testOpenSpoutCanReadXlsx()
    {
        $openSpout = new OpenSpout();
        $data = iterator_to_array($openSpout->readFile(__DIR__ . '/data/empty.xlsx'));
        $this->assertCount(0, $data);

        $openSpout = new OpenSpout();
        $data = iterator_to_array($openSpout->readFile(__DIR__ . '/data/basic.xlsx'));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);
    }

    public function testOpenSpoutCanWriteXlsx()
    {
        $openSpout = new OpenSpout();
        $string = $openSpout->writeString([
            [
                "john", "doe", "john.doe@example.com"
            ]
        ]);
        $this->assertStringContainsString('[Content_Types].xml', $string);

        $openSpout = new OpenSpout();
        $string2 = $openSpout->writeString([
            [
                "fname", "sname", "email"
            ],
            [
                "john", "doe", "john.doe@example.com"
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
    }

    public function testSpreadsheetCanWriteXlsx()
    {
        $openSpout = new PhpSpreadsheet();
        $string = $openSpout->writeString([
            [
                "john", "doe", "john.doe@example.com"
            ]
        ]);
        $this->assertStringContainsString('[Content_Types].xml', $string);

        $openSpout = new PhpSpreadsheet();
        $string2 = $openSpout->writeString([
            [
                "fname", "sname", "email"
            ],
            [
                "john", "doe", "john.doe@example.com"
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
    }

    public function testSimpleCanWriteXlsx()
    {
        $openSpout = new Simple();
        $string = $openSpout->writeString([
            [
                "john", "doe", "john.doe@example.com"
            ]
        ]);
        $this->assertStringContainsString('[Content_Types].xml', $string);

        $openSpout = new Simple();
        $string2 = $openSpout->writeString([
            [
                "fname", "sname", "email"
            ],
            [
                "john", "doe", "john.doe@example.com"
            ]
        ], ...[
            'autofilter' => 'A1:C1',
            'freezePane' => 'A1',
        ]);
        $this->assertStringContainsString('[Content_Types].xml', $string);
        $this->assertNotEquals($string, $string2);
    }
}
