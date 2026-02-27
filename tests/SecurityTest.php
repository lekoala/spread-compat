<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Tests;

use PHPUnit\Framework\TestCase;
use LeKoala\SpreadCompat\Csv\Native;
use LeKoala\SpreadCompat\Csv\League;
use LeKoala\SpreadCompat\Csv\OpenSpout;
use LeKoala\SpreadCompat\Csv\PhpSpreadsheet;

class SecurityTest extends TestCase
{
    public function testNativeEscapesFormulas(): void
    {
        $data = [
            ['Name', 'Formula'],
            ['John Doe', '=SUM(1,2)'],
            ['Jane Doe', '+1+1'],
            ['Admin', '-5'],
            ['AtSymbol', '@Something'],
            ['Tab', "\tSomething"],
            ['CR', "\rSomething"],
        ];

        $adapter = new Native();
        $adapter->escapeFormulas = true;
        $csv = $adapter->writeString($data);

        $this->assertStringContainsString("'=SUM(1,2)", $csv);
        $this->assertStringContainsString("'+1+1", $csv);
        $this->assertStringContainsString("'-5", $csv);
        $this->assertStringContainsString("'@Something", $csv);
        $this->assertStringContainsString("'\tSomething", $csv);
        $this->assertStringContainsString("'\rSomething", $csv);
    }

    public function testNativeDoesNotEscapeFormulasByDefault(): void
    {
        $data = [
            ['Name', 'Formula'],
            ['John Doe', '=SUM(1,2)'],
        ];

        $adapter = new Native();
        $csv = $adapter->writeString($data);

        $this->assertStringNotContainsString("'=SUM(1,2)", $csv);
        $this->assertStringContainsString("=SUM(1,2)", $csv);
    }

    public function testNativeEscapesHeaders(): void
    {
        $data = [
            ['John Doe', '1'],
        ];

        $adapter = new Native();
        $adapter->headers = ['Name', '=Formula()'];
        $adapter->escapeFormulas = true;
        $csv = $adapter->writeString($data);

        $this->assertStringContainsString("'=Formula()", $csv);
    }

    public function testLeagueEscapesFormulas(): void
    {
        if (!class_exists(\League\Csv\Writer::class)) {
            $this->markTestSkipped('League CSV not installed');
        }
        $data = [
            ['Name', 'Formula'],
            ['John Doe', '=SUM(1,2)'],
        ];

        $adapter = new League();
        $adapter->escapeFormulas = true;
        $csv = $adapter->writeString($data);

        $this->assertStringContainsString("'=SUM(1,2)", $csv);
    }

    public function testOpenSpoutEscapesFormulas(): void
    {
        if (!class_exists(\OpenSpout\Writer\CSV\Writer::class)) {
            $this->markTestSkipped('OpenSpout not installed');
        }
        $data = [
            ['Name', 'Formula'],
            ['John Doe', '=SUM(1,2)'],
        ];

        $adapter = new OpenSpout();
        $adapter->escapeFormulas = true;
        $csv = $adapter->writeString($data);

        $this->assertStringContainsString("'=SUM(1,2)", $csv);
    }

    public function testPhpSpreadsheetEscapesFormulas(): void
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            $this->markTestSkipped('PhpSpreadsheet not installed');
        }
        $data = [
            ['Name', 'Formula'],
            ['John Doe', '=SUM(1,2)'],
        ];

        $adapter = new PhpSpreadsheet();
        $adapter->escapeFormulas = true;
        $csv = $adapter->writeString($data);

        $this->assertStringContainsString("'=SUM(1,2)", $csv);
    }
}
