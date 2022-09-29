<?php

declare(strict_types=1);

namespace Renttek\Attributes\Attributes;

use Assert\Assertion;
use Attribute;
use Renttek\Attributes\Attributes\Webapi\Parameter;
use Renttek\Attributes\Attributes\Webapi\Resource;

#[Attribute(Attribute::TARGET_CLASS)]
class Webapi
{
    /**
     * @param list<Resource> $resources
     * @param list<Parameter> $parameters
     */
    public function __construct(
        public string $path = '',
        public ?array $resources = null,
        public ?array $parameters = null,
        public ?int $inputArraySizeLimit = null,
    ) {
        if ($this->resources !== null) {
            Assertion::allIsInstanceOf($this->resources, Resource::class);
        }

        if ($this->parameters !== null) {
            Assertion::allIsInstanceOf($this->parameters, Parameter::class);
        }
    }
}
