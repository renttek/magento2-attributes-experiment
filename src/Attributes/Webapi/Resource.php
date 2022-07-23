<?php

declare(strict_types=1);

namespace Renttek\Attributes\Attributes\Webapi;

class Resource
{
    public function __construct(
        public string $ref
    ) {
    }
}
