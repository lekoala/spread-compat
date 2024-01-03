<?php

declare(strict_types=1);

namespace LeKoala\SpreadCompat\Common;

use Exception;

trait Configure
{
    public function configure(...$opts): void
    {
        foreach ($opts as $k => $v) {
            // It's an Options class
            if ($v instanceof Options) {
                $this->configure(...get_object_vars($v));
                return;
            }
            // If you passed the array directly instead of ...$opts
            if (is_numeric($k)) {
                throw new Exception("Invalid key");
            }
            // Ignore invalid properties for this adapter
            if (!property_exists($this, $k)) {
                continue;
            }
            $this->$k = $v;
        }
    }
}
