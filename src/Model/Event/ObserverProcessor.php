<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model\Event;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use ReflectionClass;
use Renttek\Attributes\Attributes\EventSubscriber;
use Renttek\Attributes\Model\ClassProcessorInterface;

use function Renttek\Attributes\Functions\hasAttribute;
use function Renttek\Attributes\Functions\isObserver;

class ObserverProcessor implements ClassProcessorInterface
{
    public function process(ReflectionClass $reflection): iterable
    {
        $observerAttributes = $reflection->getAttributes(EventSubscriber::class);
        foreach ($observerAttributes as $observerAttribute) {
            /** @var EventSubscriber $observer */
            $observer = $observerAttribute->newInstance();

            yield [
                'area'     => $observer->area,
                'event'    => $observer->event,
                'instance' => $reflection->getName(),
                'name'     => $observer->name ?? $this->getEventName($reflection),
                'shared'   => $observer->shared,
                'disabled' => $observer->disabled,
            ];
        }
    }

    public function supports(Interface_|Class_ $class): bool
    {
        return $class instanceof Class_
            && isObserver($class)
            && hasAttribute($class, Observer::class);
    }

    private function getEventName(ReflectionClass $reflection): string
    {
        return strtolower(str_replace('\\', '_', $reflection->getName()));
    }
}
