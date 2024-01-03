<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Common;

class Options
{
    use Configure;

    // Common
    public bool $assoc = false;
    /**
     * @var string[]
     */
    public array $headers = [];

    // Csv only
    public string $separator = ",";
    public string $enclosure = "\"";
    public string $escape = "\\";
    public string $eol = "\n";
    public ?string $inputEncoding = null;
    public ?string $outputEncoding = null;
    public bool $bom = true;

    // Excel only
    public ?string $creator = null;
    public ?string $autofilter = null;
    public ?string $freezePane = null;

    public function __construct(...$opts)
    {
        if (!empty($opts)) {
            $this->configure(...$opts);
        }
    }
}
