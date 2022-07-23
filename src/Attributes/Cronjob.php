<?php

declare(strict_types=1);

namespace Renttek\Attributes\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Cronjob
{
    public function __construct(
        public string $name,
        public ?string $schedule = null,
        public ?string $configPath = null,
        public string $group = 'default',
    ) {
    }
}
