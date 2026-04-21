<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Csv;

use LeKoala\SpreadCompat\SpreadInterface;
use LeKoala\SpreadCompat\Common\Configure;
use LeKoala\SpreadCompat\Common\ReadWriteString;

abstract class CsvAdapter implements SpreadInterface
{
    use Configure;
    use ReadWriteString;

    public string $separator = ",";
    public string $enclosure = "\"";
    public string $escape = "\\";
    public string $eol = "\n";
    public ?string $inputEncoding = null;
    public ?string $outputEncoding = null;
    public bool $assoc = false;
    public bool $bom = true;
    /**
     * @var string[]
     */
    public array $headers = [];
    public bool $escapeFormulas = false;

    public function getInputEncoding(): ?string
    {
        if ($this->inputEncoding) {
            return strtoupper($this->inputEncoding);
        }
        return null;
    }

    public function getOutputEncoding(): ?string
    {
        if ($this->outputEncoding) {
            return strtoupper($this->outputEncoding);
        }
        return null;
    }

    /**
     * @param resource|string|false $streamOrFile
     */
    public function configureSeparator($streamOrFile = null): void
    {
        if (!$streamOrFile) {
            return;
        }
        if ($this->separator === "auto") {
            $this->separator = $this->detectSeparator($streamOrFile);
        }
    }

    public function getSeparator(): string
    {
        if ($this->separator === "auto") {
            return ",";
        }
        return $this->separator;
    }

    /**
     * @param resource|string $streamOrFile
     * @return string
     */
    public function detectSeparator($streamOrFile = null): string
    {
        // Default separator
        if ($streamOrFile === null) {
            return ',';
        }

        $line = "";
        if (is_string($streamOrFile)) {
            if (is_file($streamOrFile)) {
                $h = fopen($streamOrFile, 'r');
                if ($h) {
                    $line = fgets($h);
                    fclose($h);
                }
            } else {
                $line = preg_split('/\r\n|\r|\n/', $streamOrFile, 2);
                $line = $line[0] ?? "";
            }
        } elseif (is_resource($streamOrFile)) {
            $line = fgets($streamOrFile);
            rewind($streamOrFile);
        }

        if ($line === false) {
            return ',';
        }

        // Remove enclosures to avoid false positives
        $line = preg_replace("/\"[^\"]*\"/", "", $line) ?? "";

        foreach ([',', ';', '|', "\t"] as $separator) {
            if (str_contains($line, $separator)) {
                return $separator;
            }
        }

        return ',';
    }

    /**
     * @template T of array<mixed>
     * @param T $row
     * @return T
     */
    protected function escapeRow(array $row): array
    {
        if (!$this->escapeFormulas) {
            return $row;
        }
        $escapedRow = [];
        foreach ($row as $k => $cell) {
            if (is_string($cell) && $cell !== '') {
                $firstChar = $cell[0];
                if (
                    $firstChar === '=' ||
                    $firstChar === '+' ||
                    $firstChar === '-' ||
                    $firstChar === '@' ||
                    $firstChar === "\t" ||
                    $firstChar === "\r"
                ) {
                    $cell = "'" . $cell;
                }
            }
            $escapedRow[$k] = $cell;
        }
        /** @var T */
        return $escapedRow;
    }
}
