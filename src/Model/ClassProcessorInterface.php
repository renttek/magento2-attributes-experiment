<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use ReflectionClass;

interface ClassProcessorInterface
{
    /**
     * @param ReflectionClass<object> $reflection
     *
     * @return iterable<array<string, mixed>>
     */
    public function process(ReflectionClass $reflection): iterable;
    public function supports(Class_ | Interface_ $class): bool;
}
