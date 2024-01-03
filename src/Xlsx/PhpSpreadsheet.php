<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Xlsx;

use LeKoala\SpreadCompat\Common\PhpSpreadsheetUtils;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;

class PhpSpreadsheet extends XlsxAdapter
{
    use PhpSpreadsheetUtils;

    protected function getReaderClass(): string
    {
        return ReaderXlsx::class;
    }

    protected function getWriterClass(): string
    {
        return WriterXlsx::class;
    }

    protected function getMimetype(): string
    {
        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    }
}
