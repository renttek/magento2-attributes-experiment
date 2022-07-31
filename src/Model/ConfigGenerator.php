<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model;

use ReflectionClass;

class ConfigGenerator
{
    /**
     * @param list<string> $paths
     */
    public function __construct(
        private readonly ClassFinder $classFinder,
        private readonly ClassReader $classReader,
        private readonly ClassProcessorInterface $processor,
        private readonly array $paths = [],
    ) {
    }

    /**
     * @return iterable<array<string, mixed>>
     */
    public function generate(): iterable
    {
        $classFiles = $this->classFinder->findClasses($this->paths);

        foreach ($classFiles as $classFile) {
            $classNodes = $this->classReader->getClassNodes($classFile) ?? [];
            foreach ($classNodes as $classNode) {
                if (!$this->processor->supports($classNode)) {
                    continue;
                }

                $className = $classNode->namespacedName->toString();
                try {
                    $reflection = new ReflectionClass($className);
                } catch (\ReflectionException $e) {
                    continue;
                }

                yield from $this->processor->process($reflection);
            }
        }
    }
}
