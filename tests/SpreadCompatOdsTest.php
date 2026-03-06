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
}
