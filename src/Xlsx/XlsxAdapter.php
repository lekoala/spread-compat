<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Xlsx;

use LeKoala\SpreadCompat\Common\Configure;
use LeKoala\SpreadCompat\SpreadInterface;

abstract class XlsxAdapter implements SpreadInterface
{
    use Configure;

    public bool $assoc = false;
    public ?string $creator = null;
    public ?string $autofilter = null;
    public ?string $freezePane = null;
}
