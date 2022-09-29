<?php

declare(strict_types=1);

namespace Renttek\Attributes\Attributes\Webapi;

use Attribute;
use Renttek\Attributes\Attributes\Webapi;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route extends Webapi
{
    /**
     * @param list<Resource> $resources
     * @param list<Parameter> $parameters
     */
    public function __construct(
        public string $path,
        public string $method,
        public bool $secure = true,
        public ?string $soapOperation = null,
        public ?array $resources = null,
        public ?array $parameters = null,
        public ?int $inputArraySizeLimit = null,
    ) {
        parent::__construct($this->path, $this->resources, $this->parameters, $this->inputArraySizeLimit);
    }
}
