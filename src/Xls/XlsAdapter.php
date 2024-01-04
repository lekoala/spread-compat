<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Xls;

use LeKoala\SpreadCompat\Common\Configure;
use LeKoala\SpreadCompat\Common\ReadWriteString;
use LeKoala\SpreadCompat\SpreadInterface;

abstract class XlsAdapter implements SpreadInterface
{
    use Configure;
    use ReadWriteString;

    public bool $assoc = false;
    public ?string $creator = null;
    public ?string $autofilter = null;
    public ?string $freezePane = null;
}
