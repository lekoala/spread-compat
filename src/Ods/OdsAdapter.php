<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Ods;

use LeKoala\SpreadCompat\SpreadInterface;
use LeKoala\SpreadCompat\Common\Configure;
use LeKoala\SpreadCompat\Common\ReadWriteString;

abstract class OdsAdapter implements SpreadInterface
{
    use Configure;
    use ReadWriteString;

    public bool $assoc = false;
    public ?string $creator = null;
    public ?string $title = null;
    public ?string $subject = null;
    public ?string $keywords = null;
    public ?string $description = null;
    public ?string $category = null;
    public bool $stream = false;
    public ?string $tempPath = null;
    /**
     * @var string[]
     */
    public array $headers = [];
}
