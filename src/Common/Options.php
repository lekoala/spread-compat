<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Common;

use InvalidArgumentException;

/**
 * Options regroup all available options for all adapters. Unsupported options
 * are ignored by adapters, but the constructor is strict about unknown names.
 */
class Options
{
    use Configure;

    // Common
    public bool $assoc = false;
    public ?string $adapter = null;
    public ?string $extension = null;
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
    public bool $escapeFormulas = false;

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

    // Excel only
    /**
     * Whether supported adapters stream browser output instead of buffering it.
     */
    public bool $stream = false;

    public function __construct(...$opts)
    {
        if (empty($opts)) {
            return;
        }

        $normalized = self::normalize($opts);
        $unknown = array_diff(array_keys($normalized), array_keys(get_object_vars($this)));
        if ($unknown !== []) {
            throw new InvalidArgumentException("Unknown option(s): " . implode(', ', $unknown));
        }

        $this->configure(...$opts);
    }

    /**
     * Flatten variadic options (named arguments, arrays and Options instances)
     * into a single canonical array.
     *
     * @param array<mixed> $opts
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public static function normalize(array $opts): array
    {
        $result = [];

        foreach ($opts as $key => $value) {
            if ($value instanceof self) {
                foreach ($value->toArray() as $k => $v) {
                    $result[$k] = $v;
                }
                continue;
            }

            if (is_int($key)) {
                if (!is_array($value)) {
                    throw new InvalidArgumentException('Invalid options');
                }
                foreach ($value as $k => $v) {
                    $result[$k] = $v;
                }
                continue;
            }

            $result[$key] = $value;
        }

        /** @var array<string, mixed> $result */
        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        /** @var array<string, mixed> $vars */
        $vars = get_object_vars($this);
        return $vars;
    }
}
