<?php

declare(strict_types=1);

namespace Renttek\Attributes\Attributes;

use Attribute;
use Magento\Framework\App\Area;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class EventSubscriber
{
    /**
     * @psalm-param Area::AREA_* $area
     */
    public function __construct(
        public string $event,
        public ?string $name = null,
        public ?string $area = null,
        public bool $disabled = false,
        public bool $shared = false,
    ) {
    }
}
