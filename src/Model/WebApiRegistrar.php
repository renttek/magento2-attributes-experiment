<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model;

use SplFileInfo;

use function iter\apply;

class WebApiRegistrar
{
    private array $registeredClasses;

    public function __construct(
        private readonly ClassReader $classReader,
        private readonly ClassFinder $classFinder,
    ) {
    }

    public function registerWebApis(): void
    {
        apply(
            fn(SplFileInfo $f) => $this->registerFile($f),
            $this->classFinder->findClasses(['Api'])
        );
    }

    /**
     * @return list<class-string>
     */
    public function getRegisteredClasses(): array
    {
        if (!isset($this->registeredClasses)) {
            $this->registerWebApis();
        }

        return $this->registeredClasses;
    }

    public function registerFile(string|SplFileInfo $path): void
    {
        if (is_string($path)) {
            $path = new SplFileInfo($path);
        }

        $className = $this->classReader->getClassNameByFile($path);
        if ($className === null) {
            return;
        }

        if (!str_contains($className, 'Foo')) {
            return;
        }

        $this->registeredClasses[] = $className;
    }
}
