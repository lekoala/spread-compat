<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Common;

trait Configure
{
    public function configure(...$opts): void
    {
        foreach (Options::normalize($opts) as $key => $value) {
            // Ignore invalid properties for this adapter
            if (!property_exists($this, $key)) {
                continue;
            }
            $this->$key = $value;
        }
    }
}
