<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Common;

use ZipArchive;

class ZipUtils
{
    public static function zipError(int $code): string
    {
        return match ($code) {
            ZipArchive::ER_EXISTS => 'File already exists.',
            ZipArchive::ER_INCONS => 'Zip archive inconsistent.',
            ZipArchive::ER_INVAL => 'Invalid argument.',
            ZipArchive::ER_MEMORY => 'Malloc failure.',
            ZipArchive::ER_NOENT => 'No such file.',
            ZipArchive::ER_NOZIP => 'Not a zip archive.',
            ZipArchive::ER_OPEN => 'Can\'t open file.',
            ZipArchive::ER_READ => 'Read error.',
            ZipArchive::ER_SEEK => 'Seek error.',
            default => 'Unknown error code ' . $code . '.',
        };
    }

    public static function getData(ZipArchive $zip, string $name): ?string
    {
        $idx = $zip->locateName($name);
        if ($idx) {
            $result = $zip->getFromIndex($idx);
            if ($result) {
                return $result;
            }
        }
        return null;
    }
}
