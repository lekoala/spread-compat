<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Tests;

use PHPUnit\Framework\TestCase;
use LeKoala\SpreadCompat\Common\ZipUtils;
use ZipArchive;

class ZipUtilsTest extends TestCase
{
    public function testZipError()
    {
        $this->assertEquals('File already exists.', ZipUtils::zipError(ZipArchive::ER_EXISTS));
        $this->assertEquals('Zip archive inconsistent.', ZipUtils::zipError(ZipArchive::ER_INCONS));
        $this->assertEquals('Invalid argument.', ZipUtils::zipError(ZipArchive::ER_INVAL));
        $this->assertEquals('Malloc failure.', ZipUtils::zipError(ZipArchive::ER_MEMORY));
        $this->assertEquals('No such file.', ZipUtils::zipError(ZipArchive::ER_NOENT));
        $this->assertEquals('Not a zip archive.', ZipUtils::zipError(ZipArchive::ER_NOZIP));
        $this->assertEquals('Can\'t open file.', ZipUtils::zipError(ZipArchive::ER_OPEN));
        $this->assertEquals('Read error.', ZipUtils::zipError(ZipArchive::ER_READ));
        $this->assertEquals('Seek error.', ZipUtils::zipError(ZipArchive::ER_SEEK));
        $this->assertEquals('Unknown error code 999.', ZipUtils::zipError(999));
    }

    public function testGetData()
    {
        $filename = (string)tempnam(sys_get_temp_dir(), 'zip');
        try {
            $zip = new ZipArchive();
            $zip->open($filename, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            $zip->addFromString('test.txt', 'hello world');
            $zip->addFromString('zero.txt', '0');
            $zip->close();

            $zip = new ZipArchive();
            $zip->open($filename);

            // Test existing file
            $this->assertEquals('hello world', ZipUtils::getData($zip, 'test.txt'));

            // Test non-existing file
            $this->assertNull(ZipUtils::getData($zip, 'non-existing.txt'));

            // Test "0" content (index 1)
            $this->assertEquals('0', ZipUtils::getData($zip, 'zero.txt'));

            $zip->close();
        } finally {
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
    }
}
