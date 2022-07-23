<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model\Cronjob;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use ReflectionClass;
use ReflectionMethod;
use Renttek\Attributes\Attributes\Cronjob;
use Renttek\Attributes\Model\ClassProcessorInterface;

use function iter\any;
use function iter\filter;
use function Renttek\Attributes\Functions\hasAttribute;

class CronjobProcessor implements ClassProcessorInterface
{
    public function process(ReflectionClass $reflection): iterable
    {
        /** @var iterable<ReflectionMethod> $methods */
        $methods = filter(
            fn(ReflectionMethod $method) => $method->isPublic(),
            $reflection->getMethods()
        );

        foreach ($methods as $method) {
            $cronjobAttributes = $method->getAttributes(Cronjob::class);
            foreach ($cronjobAttributes as $cronjobAttribute) {
                /** @var Cronjob $cronjob */
                $cronjob = $cronjobAttribute->newInstance();

                yield [
                    'instance'   => $reflection->getName(),
                    'method'     => $method->getName(),
                    'name'       => $cronjob->name,
                    'group'      => $cronjob->group,
                    'schedule'   => $cronjob->schedule,
                    'configPath' => $cronjob->configPath,
                ];
            }
        }
    }

    public function supports(Interface_|Class_ $class): bool
    {
        return $class instanceof Class_
            && $this->hasCronjobMethods($class);
    }

    private function hasCronjobMethods(Class_ $class): bool
    {
        return any(
            fn(ClassMethod $method) => $method->isPublic() && hasAttribute($method, Cronjob::class),
            $class->getMethods()
        );
    }
}
