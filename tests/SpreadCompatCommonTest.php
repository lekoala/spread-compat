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
        $csv = new Native();
        $csv->configure($options);
        $this->assertEquals(";", $csv->separator);
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
}
