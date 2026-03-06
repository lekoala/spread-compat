<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Xlsx;

use Generator;
use LeKoala\Baresheet\XlsxReader;
use LeKoala\Baresheet\XlsxWriter;

class Baresheet extends XlsxAdapter
{
    /**
     * Is a given number format code a date/time?
     */
    public static function isDateTimeFormatCode(string $excelFormatCode): bool
    {
        // General
        if (strtolower($excelFormatCode) === 'general') {
            return false;
        }
        // Currencies, accounting
        if (str_starts_with($excelFormatCode, '_') || str_starts_with($excelFormatCode, '0 ')) {
            return false;
        }
        // "\C\H\-00000" (Switzerland) and "\D-00000" (Germany).
        if (str_contains($excelFormatCode, '-00000')) {
            return false;
        }

        $cleanCode = str_replace(['[', ']', '.000'], '', $excelFormatCode);

        // Is week
        if ($cleanCode === 'WW') {
            return true;
        }

        // Is time
        if (str_contains($cleanCode, 'h:m')) {
            return true;
        }

        // Is date
        if (str_contains($cleanCode, 'yy') || str_contains($cleanCode, 'dd') || str_contains($cleanCode, 'mm')) {
            return true;
        }

        return false;
    }

    /**
     * Gets the standard format code for a built-in Open XML number format ID.
     *
     * Note: Some formats (especially dates, times, currency) are locale-dependent.
     * The format codes returned here are common representations (often US English based),
     * but the actual display might vary in spreadsheet applications based on settings.
     * Returns null if the ID is not a recognized built-in format ID.
     *
     * @param int $numFmtId The built-in number format ID (0-163 range roughly).
     * @return string|null The corresponding format code string, or null if not found.
     */
    public static function getBuiltInFormatCode(int $numFmtId): ?string
    {
        return match ($numFmtId) {
            0 => 'General',
            1 => '0',
            2 => '0.00',
            3 => '#,##0',
            4 => '#,##0.00',
            5 => '$#,##0_);($#,##0)', // Often US Dollar, locale dependent
            6 => '$#,##0_);[Red]($#,##0)', // Often US Dollar, locale dependent
            7 => '$#,##0.00_);($#,##0.00)', // Often US Dollar, locale dependent
            8 => '$#,##0.00_);[Red]($#,##0.00)', // Often US Dollar, locale dependent
            9 => '0%',
            10 => '0.00%',
            11 => '0.00E+00',
            12 => '# ?/?',
            13 => '# ??/??',
            14 => 'm/d/yyyy', // Locale-dependent Date
            15 => 'd-mmm-yy',
            16 => 'd-mmm',
            17 => 'mmm-yy',
            18 => 'h:mm AM/PM', // Locale-dependent Time
            19 => 'h:mm:ss AM/PM', // Locale-dependent Time
            20 => 'h:mm',
            21 => 'h:mm:ss',
            22 => 'm/d/yyyy h:mm', // Locale-dependent Date & Time
            37 => '#,##0 ;(#,##0)',
            38 => '#,##0 ;[Red](#,##0)',
            39 => '#,##0.00;(#,##0.00)',
            40 => '#,##0.00;[Red](#,##0.00)',
            41 => '_(* #,##0_);_(* (#,##0);_(* "-"_);_(@_)', // Accounting
            42 => '_($* #,##0_);_($* (#,##0);_($* "-"_);_(@_)', // Accounting Currency (locale dep.)
            43 => '_(* #,##0.00_);_(* (#,##0.00);_(* "-"??_);_(@_)', // Accounting
            44 => '_($* #,##0.00_);_($* (#,##0.00);_($* "-"??_);_(@_)', // Accounting Currency (locale dep.)
            45 => 'mm:ss',
            46 => '[h]:mm:ss',
            47 => 'mm:ss.0',
            48 => '##0.0E+0',
            49 => '@', // Text format
            default => null,
        };
    }

    public function readFile(string $filename, ...$opts): Generator
    {
        $this->configure(...$opts);
        $reader = new XlsxReader();
        $reader->assoc = $this->assoc;
        return $reader->readFile($filename);
    }

    public function writeFile(iterable $data, string $filename, ...$opts): bool
    {
        $this->configure(...$opts);
        $writer = new XlsxWriter();
        $writer->stream = $this->stream;
        $writer->tempPath = $this->tempPath;
        $writer->autofilter = $this->autofilter;
        $writer->freezePane = $this->freezePane;
        /** @var iterable<array<\DateTimeInterface|float|int|string|\Stringable|null>> $data */
        return $writer->writeFile($data, $filename);
    }

    public function output(iterable $data, string $filename, ...$opts): void
    {
        $this->configure(...$opts);
        $writer = new XlsxWriter();
        $writer->stream = $this->stream;
        $writer->tempPath = $this->tempPath;
        $writer->autofilter = $this->autofilter;
        $writer->freezePane = $this->freezePane;
        /** @var iterable<array<\DateTimeInterface|float|int|string|\Stringable|null>> $data */
        $writer->output($data, $filename);
    }

    public function writeString(iterable $data, ...$opts): string
    {
        $this->configure(...$opts);
        $writer = new XlsxWriter();
        $writer->stream = $this->stream;
        $writer->tempPath = $this->tempPath;
        $writer->autofilter = $this->autofilter;
        $writer->freezePane = $this->freezePane;
        $writer->headers = $this->headers;
        /** @var iterable<array<\DateTimeInterface|float|int|string|\Stringable|null>> $data */
        return $writer->writeString($data);
    }
}
