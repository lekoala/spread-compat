<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Xls;

use LeKoala\SpreadCompat\Common\PhpSpreadsheetUtils;
use PhpOffice\PhpSpreadsheet\Reader\Xls as ReaderXls;
use PhpOffice\PhpSpreadsheet\Writer\Xls as WriterXls;

class PhpSpreadsheet extends XlsAdapter
{
    use PhpSpreadsheetUtils;

    protected function getReaderClass(): string
    {
        return ReaderXls::class;
    }

    protected function getWriterClass(): string
    {
        return WriterXls::class;
    }

    protected function getMimetype(): string
    {
        return 'application/vnd.ms-excel';
    }
}
