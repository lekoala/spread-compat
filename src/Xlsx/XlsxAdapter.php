<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Xlsx;

use LeKoala\SpreadCompat\SpreadInterface;
use LeKoala\SpreadCompat\Common\Configure;
use LeKoala\SpreadCompat\Common\ReadWriteString;

abstract class XlsxAdapter implements SpreadInterface
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
    public ?string $language = null;
    public ?string $autofilter = null;
    public ?string $freezePane = null;
}
