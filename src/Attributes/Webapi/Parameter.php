<?php

declare(strict_types=1);

namespace Renttek\Attributes\Attributes\Webapi;

class Parameter
{
    public function __construct(
        public string $name,
        public string $value,
        public bool $force = false,
    ) {
    }
}
