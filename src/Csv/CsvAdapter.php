<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Csv;

use LeKoala\SpreadCompat\SpreadInterface;

abstract class CsvAdapter implements SpreadInterface
{
    public string $separator = ",";
    public string $enclosure = "\"";
    public string $escape = "\\";
    public string $eol = "\n";
    public ?string $inputEncoding = null;
    public ?string $outputEncoding = null;
    public bool $assoc = false;
    public bool $bom = true;
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

    public function configure(...$opts): void
    {
        foreach ($opts as $k => $v) {
            $this->$k = $v;
        }
    }
}
