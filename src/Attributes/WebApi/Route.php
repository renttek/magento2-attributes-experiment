<?php

declare(strict_types=1);

namespace Renttek\Attributes\Attributes\WebApi;

use Attribute;
use Renttek\Attributes\Attributes\WebApi;

use function sprintf;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route extends WebApi
{
    /**
     * @param list<Resource> $resources
     */
    public function __construct(
        public string $path,
        public string $method,
        public bool $secure = true,
        public ?string $soapOperation = null,
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
        parent::__construct($this->path, $this->resources, $this->parameters, $this->inputArraySizeLimit);
    }
}
