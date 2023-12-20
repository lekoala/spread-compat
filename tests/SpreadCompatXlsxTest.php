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
}
