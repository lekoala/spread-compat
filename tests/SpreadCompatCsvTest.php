<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Tests;

use PHPUnit\Framework\TestCase;
use LeKoala\SpreadCompat\Csv\League;
use LeKoala\SpreadCompat\Csv\Native;
use LeKoala\SpreadCompat\Csv\OpenSpout;
use LeKoala\SpreadCompat\Csv\PhpSpreadsheet;
use LeKoala\SpreadCompat\SpreadCompat;

class SpreadCompatCsvTest extends TestCase
{
    public function testFacadeCanReadCsv()
    {
        $data = iterator_to_array(SpreadCompat::read(__DIR__ . '/data/basic.csv'));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);
    }

    public function testAutoSeparator()
    {
        SpreadCompat::$preferredCsvAdapter = SpreadCompat::NATIVE;
        $data = iterator_to_array(SpreadCompat::read(__DIR__ . '/data/auto.csv', separator: 'auto'));
        $this->assertCount(101, $data);
        $this->assertCount(4, $data[0]);

        $data = iterator_to_array(SpreadCompat::read(__DIR__ . '/data/auto2.csv', separator: 'auto'));
        $this->assertCount(101, $data);
        $this->assertCount(4, $data[0]);
        SpreadCompat::$preferredCsvAdapter = null;
    }

    public function testOpenSpoutCanReadCsv()
    {
        $openSpout = new OpenSpout();
        $data = iterator_to_array($openSpout->readString('john,doe,john.doe@example.com'));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);

        $openSpout = new OpenSpout();
        $data = iterator_to_array($openSpout->readFile(__DIR__ . '/data/empty.csv'));
        $this->assertCount(0, $data);

        $openSpout = new OpenSpout();
        $data = iterator_to_array($openSpout->readFile(__DIR__ . '/data/basic.csv'));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);

        $openSpout = new OpenSpout();
        $data = iterator_to_array($openSpout->readFile(__DIR__ . '/data/separator.csv', separator: ";"));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);

        $openSpout = new OpenSpout();
        $data = iterator_to_array($openSpout->readFile(__DIR__ . '/data/headers.csv', assoc: true));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);
        $this->assertArrayHasKey('email', $data[0]);

        $openSpout = new OpenSpout();
        $data = iterator_to_array($openSpout->readFile(__DIR__ . '/data/auto2.csv', separator: 'auto'));
        $this->assertCount(101, $data);
        $this->assertCount(4, $data[0]);
        SpreadCompat::$preferredCsvAdapter = null;
    }

    public function testOpenSpoutCanWriteCsv()
    {
        $openSpout = new OpenSpout();
        $openSpout->bom = false;
        $string = $openSpout->writeString([
            [
                "john", "doe", "john.doe@example.com"
            ]
        ]);
        $expected = file_get_contents(__DIR__ . '/data/basic.csv');
        $this->assertEquals($expected, $string);

        $openSpout = new OpenSpout();
        $openSpout->bom = true;
        $string = $openSpout->writeString([
            [
                "john", "doe", "john.doe@example.com"
            ]
        ]);
        $expected = file_get_contents(__DIR__ . '/data/bom.csv');
        $this->assertEquals($expected, $string);

        // Spout cannot force enclosure, it only adds as necessary
        $openSpout = new OpenSpout();
        $openSpout->bom = false;
        $openSpout->separator = ";";
        $string = $openSpout->writeString([
            [
                "john\"john", "doe", "john.doe@example.com"
            ]
        ]);
        $expected = file_get_contents(__DIR__ . '/data/separator.csv');
        $this->assertEquals($expected, $string);
    }

    public function testLeagueCanReadCsv()
    {
        $league = new League();
        $data = iterator_to_array($league->readString('john,doe,john.doe@example.com'));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);

        $league = new League();
        $data = iterator_to_array($league->readFile(__DIR__ . '/data/empty.csv'));
        $this->assertCount(0, $data);

        $league = new League();
        $data = iterator_to_array($league->readFile(__DIR__ . '/data/basic.csv'));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);

        $league = new League();
        $data = iterator_to_array($league->readFile(__DIR__ . '/data/separator.csv', separator: ";"));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);

        $league = new League();
        $data = iterator_to_array($league->readFile(__DIR__ . '/data/headers.csv', assoc: true));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);
        $this->assertArrayHasKey('email', $data[0]);

        $league = new League();
        $data = iterator_to_array($league->readFile(__DIR__ . '/data/auto2.csv', separator: 'auto'));
        $this->assertCount(101, $data);
        $this->assertCount(4, $data[0]);
        SpreadCompat::$preferredCsvAdapter = null;
    }

    public function testLeagueCanWriteCsv()
    {
        $league = new League();
        $league->bom = false;
        $string = $league->writeString([
            [
                "john", "doe", "john.doe@example.com"
            ]
        ]);
        $expected = file_get_contents(__DIR__ . '/data/basic.csv');
        $this->assertEquals($expected, $string);

        $league = new League();
        $league->bom = true;
        $string = $league->writeString([
            [
                "john", "doe", "john.doe@example.com"
            ]
        ]);
        $expected = file_get_contents(__DIR__ . '/data/bom.csv');
        $this->assertEquals($expected, $string);

        // League can force enclosure https://csv.thephpleague.com/9.0/writer/#force-enclosure
        $league = new League();
        $league->bom = false;
        $league->separator = ";";
        $string = $league->writeString([
            [
                "john\"john", "doe", "john.doe@example.com"
            ]
        ]);
        $expected = file_get_contents(__DIR__ . '/data/separator.csv');
        $this->assertEquals($expected, $string);

        $native = new League();
        $stream = fopen(__DIR__ . '/data/headers.csv', 'r');
        $data = iterator_to_array($native->readStream($stream, assoc: true));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);
        $this->assertArrayHasKey('email', $data[0]);
    }

    public function testNativeCanReadCsv()
    {
        $native = new Native();
        $data = iterator_to_array($native->readString('john,doe,john.doe@example.com'));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);

        $native = new Native();
        $data = iterator_to_array($native->readFile(__DIR__ . '/data/empty.csv'));
        $this->assertCount(0, $data);

        $native = new Native();
        $data = iterator_to_array($native->readFile(__DIR__ . '/data/basic.csv'));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);

        $native = new Native();
        $data = iterator_to_array($native->readFile(__DIR__ . '/data/separator.csv', separator: ";"));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);

        $native = new Native();
        $data = iterator_to_array($native->readFile(__DIR__ . '/data/headers.csv', assoc: true));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);
        $this->assertArrayHasKey('email', $data[0]);

        $native = new Native();
        $stream = fopen(__DIR__ . '/data/headers.csv', 'r');
        $data = iterator_to_array($native->readStream($stream, assoc: true));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);
        $this->assertArrayHasKey('email', $data[0]);

        $native = new Native();
        $data = iterator_to_array($native->readFile(__DIR__ . '/data/auto2.csv', separator: 'auto'));
        $this->assertCount(101, $data);
        $this->assertCount(4, $data[0]);
        SpreadCompat::$preferredCsvAdapter = null;
    }

    public function testNativeCanWriteCsv()
    {
        $native = new Native();
        $native->bom = false;
        $string = $native->writeString([
            [
                "john", "doe", "john.doe@example.com"
            ]
        ]);
        $expected = file_get_contents(__DIR__ . '/data/basic.csv');
        $this->assertEquals($expected, $string);

        $native = new Native();
        $native->bom = true;
        $string = $native->writeString([
            [
                "john", "doe", "john.doe@example.com"
            ]
        ]);
        $expected = file_get_contents(__DIR__ . '/data/bom.csv');
        $this->assertEquals($expected, $string);

        $native = new Native();
        $native->bom = false;
        $native->separator = ";";
        $string = $native->writeString([
            [
                "john\"john", "doe", "john.doe@example.com"
            ]
        ]);
        $expected = file_get_contents(__DIR__ . '/data/separator.csv');
        $this->assertEquals($expected, $string);
    }

    public function testSpreadsheetCanReadCsv()
    {
        $PhpSpreadsheet = new PhpSpreadsheet();
        $data = iterator_to_array($PhpSpreadsheet->readString('john,doe,john.doe@example.com'));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);

        $PhpSpreadsheet = new PhpSpreadsheet();
        $data = iterator_to_array($PhpSpreadsheet->readFile(__DIR__ . '/data/empty.csv'));
        $this->assertCount(0, $data);

        $PhpSpreadsheet = new PhpSpreadsheet();
        $data = iterator_to_array($PhpSpreadsheet->readFile(__DIR__ . '/data/basic.csv'));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);

        $PhpSpreadsheet = new PhpSpreadsheet();
        $data = iterator_to_array($PhpSpreadsheet->readFile(__DIR__ . '/data/separator.csv', separator: ";"));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);

        $PhpSpreadsheet = new PhpSpreadsheet();
        $data = iterator_to_array($PhpSpreadsheet->readFile(__DIR__ . '/data/headers.csv', assoc: true));
        $this->assertCount(1, $data);
        $this->assertCount(3, $data[0]);
        $this->assertArrayHasKey('email', $data[0]);
    }

    public function testSpreadsheetCanWriteCsv()
    {
        $PhpSpreadsheet = new PhpSpreadsheet();
        $PhpSpreadsheet->bom = false;
        $string = $PhpSpreadsheet->writeString([
            [
                "john", "doe", "john.doe@example.com"
            ]
        ]);
        $expected = file_get_contents(__DIR__ . '/data/basic.csv');
        $this->assertEquals($expected, $string);

        $PhpSpreadsheet = new PhpSpreadsheet();
        $PhpSpreadsheet->bom = true;
        $string = $PhpSpreadsheet->writeString([
            [
                "john", "doe", "john.doe@example.com"
            ]
        ]);
        $expected = file_get_contents(__DIR__ . '/data/bom.csv');
        $this->assertEquals($expected, $string);

        $PhpSpreadsheet = new PhpSpreadsheet();
        $PhpSpreadsheet->bom = false;
        $PhpSpreadsheet->separator = ";";
        $string = $PhpSpreadsheet->writeString([
            [
                "john\"john", "doe", "john.doe@example.com"
            ]
        ]);
        $expected = file_get_contents(__DIR__ . '/data/separator.csv');
        $this->assertEquals($expected, $string);
    }
}
