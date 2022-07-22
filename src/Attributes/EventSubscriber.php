<?php

declare(strict_types=1);

namespace Renttek\Attributes\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class EventSubscriber
{
    public function __construct(
        public string $event,
        public ?string $area = null
    ) {
    }
}
