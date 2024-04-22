<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Common;

use ArrayAccess;

/**
 * @implements ArrayAccess<string,mixed>
 */
class Options implements ArrayAccess
{
    use Configure;

    // Common
    public bool $assoc = false;
    public ?string $adapter = null;
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
    public ?string $title = null;
    public ?string $subject = null;
    public ?string $keywords = null;
    public ?string $description = null;
    public ?string $category = null;
    public ?string $language = null;

    // Native xlsx
    public bool $stream = false;

    public function __construct(...$opts)
    {
        if (!empty($opts)) {
            $this->configure(...$opts);
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return property_exists($this, $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->$offset ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->$offset = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->$offset = null;
    }
}
