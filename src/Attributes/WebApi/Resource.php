<?php

declare(strict_types=1);

namespace Renttek\Attributes\Attributes\WebApi;

class Resource
{
    public function __construct(
        public string $ref
    ) {
    }
}
