<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Tests;

use PHPUnit\Framework\TestCase;
use LeKoala\SpreadCompat\Csv\CsvAdapter;
use Generator;

class CsvAdapterTest extends TestCase
{
    private function getAdapter(): CsvAdapter
    {
        return new class extends CsvAdapter {
            public function readFile(string $filename, ...$opts): Generator
            {
                yield [];
            }
            public function writeFile(iterable $data, string $filename, ...$opts): bool
            {
                return true;
            }
            public function output(iterable $data, string $filename, ...$opts): void
            {
            }
        };
    }

    public function testConfigureSeparatorDoesNothingIfNoInput()
    {
        $adapter = $this->getAdapter();
        $adapter->separator = "auto";
        $adapter->configureSeparator(null);
        $this->assertEquals("auto", $adapter->separator);

        $adapter->configureSeparator(false);
        $this->assertEquals("auto", $adapter->separator);
    }

    public function testConfigureSeparatorDoesNothingIfNotAuto()
    {
        $adapter = $this->getAdapter();
        $adapter->separator = ";";
        $adapter->configureSeparator("col1,col2,col3");
        $this->assertEquals(";", $adapter->separator);
    }

    public function testConfigureSeparatorDetectsSeparatorIfAuto()
    {
        $adapter = $this->getAdapter();
        $adapter->separator = "auto";
        $adapter->configureSeparator("col1;col2;col3");
        $this->assertEquals(";", $adapter->separator);
    }

    public function testDetectSeparatorWithVariousDelimiters()
    {
        $adapter = $this->getAdapter();
        $this->assertEquals(",", $adapter->detectSeparator("a,b,c"));
        $this->assertEquals(";", $adapter->detectSeparator("a;b;c"));
        $this->assertEquals("|", $adapter->detectSeparator("a|b|c"));
        $this->assertEquals("\t", $adapter->detectSeparator("a\tb\tc"));
    }

    public function testDetectSeparatorDefaultsToComma()
    {
        $adapter = $this->getAdapter();
        $this->assertEquals(",", $adapter->detectSeparator("abc"));
        $this->assertEquals(",", $adapter->detectSeparator(""));
        $this->assertEquals(",", $adapter->detectSeparator(null));
    }

    public function testDetectSeparatorWithMultiLineString()
    {
        $adapter = $this->getAdapter();
        // This is where the preg_split limit bug might show up
        // If it's buggy, it will use the whole string and might find a separator in later lines
        $this->assertEquals(",", $adapter->detectSeparator("a b c\nd;e;f"));
    }

    public function testDetectSeparatorWithFile()
    {
        $adapter = $this->getAdapter();
        $tempFile = (string)tempnam(sys_get_temp_dir(), 'csv_test');
        file_put_contents($tempFile, "a;b;c\nd,e,f");

        try {
            $this->assertEquals(";", $adapter->detectSeparator($tempFile));
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function testDetectSeparatorWithStream()
    {
        $adapter = $this->getAdapter();
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, "a|b|c\nd,e,f");
        rewind($stream);

        $this->assertEquals("|", $adapter->detectSeparator($stream));

        // Ensure stream is rewound
        $this->assertEquals(0, ftell($stream));

        fclose($stream);
    }

    public function testDetectSeparatorIgnoresEnclosures()
    {
        $adapter = $this->getAdapter();
        // Semicolon is inside enclosure, should be ignored. Comma is outside, should be detected.
        $this->assertEquals(",", $adapter->detectSeparator('"a;b;c",d,e'));

        // If enclosure is not closed, it might behave differently but let's test standard case
        $this->assertEquals(";", $adapter->detectSeparator('"a,b,c";d;e'));
    }

    public function testGetSeparator()
    {
        $adapter = $this->getAdapter();
        $adapter->separator = "auto";
        $this->assertEquals(",", $adapter->getSeparator());

        $adapter->separator = ";";
        $this->assertEquals(";", $adapter->getSeparator());
    }
}
