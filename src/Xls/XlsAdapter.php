<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Xls;

use LeKoala\SpreadCompat\SpreadInterface;

abstract class XlsAdapter implements SpreadInterface
{
    public bool $assoc = false;
    public ?string $creator = null;
    public ?string $autofilter = null;
    public ?string $freezePane = null;

    public function configure(...$opts): void
    {
        foreach ($opts as $k => $v) {
            $this->$k = $v;
        }
    }
}
