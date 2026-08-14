<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Tests;

use PHPUnit\Framework\TestCase;
use LeKoala\SpreadCompat\SpreadCompat;
use LeKoala\SpreadCompat\Ods\Baresheet;
use LeKoala\SpreadCompat\Ods\OpenSpout;

class SpreadCompatOdsTest extends TestCase
{
    public function testFacadeCanReadOds()
    {
        // We need an ods file in tests/data
        $file = __DIR__ . '/data/basic.ods';
        if (!is_file($file)) {
            $this->markTestSkipped("No basic.ods file found");
        }
        $data = iterator_to_array(SpreadCompat::read($file));
        self::assertCount(1, $data);
        self::assertCount(3, $data[0]);
    }

    public function testBaresheetCanWriteOds()
    {
        $baresheet = new Baresheet();
        $data = [
            ['fname', 'sname', 'email'],
            ['john', 'doe', 'john.doe@example.com']
        ];
        $string = $baresheet->writeString($data);
        self::assertNotEmpty($string);

        // Read it back
        $decoded = iterator_to_array($baresheet->readString($string, assoc: true));
        self::assertCount(1, $decoded);
        self::assertEquals('john', $decoded[0]['fname']);
    }

    public function testOpenSpoutCanWriteOds()
    {
        if (!class_exists(\OpenSpout\Writer\ODS\Writer::class)) {
            $this->markTestSkipped("OpenSpout ODS writer not found");
        }
        $openSpout = new OpenSpout();
        $data = [
            ['fname', 'sname', 'email'],
            ['john', 'doe', 'john.doe@example.com']
        ];
        $string = $openSpout->writeString($data);
        self::assertNotEmpty($string);

        // Read it back with Baresheet
        $baresheet = new Baresheet();
        $decoded = iterator_to_array($baresheet->readString($string, assoc: true));
        self::assertCount(1, $decoded);
        self::assertEquals('john', $decoded[0]['fname']);
    }

    public function testBaresheetWritesHeadersAndMeta()
    {
        $baresheet = new Baresheet();
        $data = [
            ['john', 'doe'],
            ['jane', 'roe'],
        ];
        $string = $baresheet->writeString(
            $data,
            headers: ['firstname', 'surname'],
            creator: 'test',
            title: 'My title'
        );
        self::assertNotEmpty($string);

        $decoded = iterator_to_array($baresheet->readString($string, assoc: true));
        self::assertEquals('john', $decoded[0]['firstname']);
        self::assertEquals('jane', $decoded[1]['firstname']);

        $tmpFile = SpreadCompat::getTempFilename() . '.ods';
        file_put_contents($tmpFile, $string);
        $props = \LeKoala\Baresheet\Spread::getProperties($tmpFile);
        self::assertEquals('test', $props['meta']['creator']);
        self::assertEquals('My title', $props['meta']['title']);
        unlink($tmpFile);
    }

    public function testBaresheetWritesHeadersToFile()
    {
        $baresheet = new Baresheet();
        $data = [
            ['john', 'doe'],
            ['jane', 'roe'],
        ];
        $tmpFile = SpreadCompat::getTempFilename() . '.ods';
        $baresheet->writeFile($data, $tmpFile, headers: ['firstname', 'surname'], creator: 'test');
        $decoded = iterator_to_array($baresheet->readFile($tmpFile, assoc: true));
        self::assertEquals('john', $decoded[0]['firstname']);

        $props = \LeKoala\Baresheet\Spread::getProperties($tmpFile);
        self::assertEquals('test', $props['meta']['creator']);
        unlink($tmpFile);
    }

    public function testFacadeCanWriteOdsToFile()
    {
        $data = [
            ['fname', 'sname', 'email'],
            ['john', 'doe', 'john.doe@example.com'],
        ];
        $tmpFile = SpreadCompat::getTempFilename() . '.ods';
        $res = SpreadCompat::write($data, $tmpFile);
        self::assertTrue($res);
        self::assertTrue(is_file($tmpFile));

        $decoded = iterator_to_array(SpreadCompat::read($tmpFile, assoc: true));
        self::assertCount(1, $decoded);
        self::assertEquals('john', $decoded[0]['fname']);
        unlink($tmpFile);
    }

    public function testFacadeOdsHeaders()
    {
        $data = [
            ['john', 'doe'],
            ['jane', 'roe'],
        ];
        $string = SpreadCompat::writeString(
            $data,
            'ods',
            adapter: SpreadCompat::BARESHEET,
            headers: ['firstname', 'surname']
        );
        self::assertNotEmpty($string);

        $decoded = iterator_to_array(
            SpreadCompat::readString($string, 'ods', adapter: SpreadCompat::BARESHEET, assoc: true)
        );
        self::assertEquals('john', $decoded[0]['firstname']);
        self::assertEquals('jane', $decoded[1]['firstname']);
    }
}
