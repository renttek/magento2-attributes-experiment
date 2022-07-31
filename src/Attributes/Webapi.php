<?php

declare(strict_types=1);

namespace Renttek\Attributes\Attributes;

use Attribute;
use Renttek\Attributes\Attributes\Webapi\Parameter;
use Renttek\Attributes\Attributes\Webapi\Resource;
use TypeError;

#[Attribute(Attribute::TARGET_CLASS)]
class Webapi
{
    /**
     * @param list<Resource> $resources
     */
    public function __construct(
        public string $path = '',
        /**
         * @var list<Resource>
         */
        public ?array $resources = null,
        /**
         * @var list<Parameter>
         */
        public ?array $parameters = null,
        public ?int $inputArraySizeLimit = null,
    ) {
        $this->assertType($this->resources, 'resources', Resource::class);
        $this->assertType($this->parameters, 'data', Parameter::class);
    }

    /**
     * @param list<mixed> $array
     * @param class-string $class
     */
    private function assertType(?array $array, string $property, string $class): void
    {
        if ($array === null) {
            return;
        }

        foreach ($array as $item) {
            if (!$item instanceof $class) {
                throw new TypeError(sprintf(
                    'All elements of $%s must be instances of %s, got %s',
                    $property,
                    Resource::class,
                    is_object($item) ? get_class($item) : gettype($item)
                ));
            }
        }
    }
}
