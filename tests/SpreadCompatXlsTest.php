<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Tests;

use PHPUnit\Framework\TestCase;
use LeKoala\SpreadCompat\SpreadCompat;
use LeKoala\SpreadCompat\Xls\PhpSpreadsheet;

class SpreadCompatXlsTest extends TestCase
{
    public function testFacadeCanReadXls()
    {
        $adapter = SpreadCompat::getAdapterName('xls');
        self::assertEquals("PhpSpreadsheet", $adapter);

        $data = iterator_to_array(SpreadCompat::read(__DIR__ . '/data/basic.xls'));
        self::assertCount(1, $data);
        self::assertCount(3, $data[0]);
    }

    public function testSpreadsheetCanReadXls()
    {
        $PhpSpreadsheet = new PhpSpreadsheet();
        $data = iterator_to_array($PhpSpreadsheet->readFile(__DIR__ . '/data/basic.xls'));
        self::assertCount(1, $data);
        self::assertCount(3, $data[0]);

        $PhpSpreadsheet = new PhpSpreadsheet();
        $data = iterator_to_array($PhpSpreadsheet->readFile(__DIR__ . '/data/header.xls', assoc: true));
        self::assertCount(1, $data);
        self::assertCount(4, $data[0]);
    }

    public function testSpreadsheetCanWriteXls()
    {
        $openSpout = new PhpSpreadsheet();
        $string = $openSpout->writeString([
            [
                "john", "doe", "john.doe@example.com"
            ]
        ]);

        $openSpout = new PhpSpreadsheet();
        $string2 = $openSpout->writeString([
            [
                "firstname", "surname", "email"
            ],
            [
                "john", "doe", "john.doe@example.com"
            ]
        ], ...[
            'autofilter' => 'A1:C1',
            'freezePane' => 'A1',
        ]);
        self::assertNotEquals($string, $string2);
    }
}
