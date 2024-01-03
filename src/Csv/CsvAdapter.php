<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Csv;

use LeKoala\SpreadCompat\SpreadInterface;
use LeKoala\SpreadCompat\Common\Configure;

abstract class CsvAdapter implements SpreadInterface
{
    use Configure;

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
                $streamOrFile = fopen($streamOrFile, 'r');
            } else {
                $line = preg_split('/\r\n|\r|\n/', $streamOrFile, 1);
                $line = $line[0] ?? "";
            }
        }
        if (is_resource($streamOrFile)) {
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
}
