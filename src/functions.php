<?php

declare(strict_types=1);

namespace Renttek\Attributes\Functions;

use Magento\Framework\Event\ObserverInterface;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;

use function iter\any;
use function iter\flatMap;
use function iter\isIterable;
use function iter\map;
use function iter\toArray;

function isObserver(Class_ $class): bool
{
    return implementsInterface($class, ObserverInterface::class);
}

/**
 * @param class-string $interface
 */
function implementsInterface(Class_ $class, string $interface): bool
{
    return isIterable($class->implements)
        && any(
            fn(Name $name) => $interface === $name->toString(),
            $class->implements
        );
}

/**
 * @param class-string $attribute
 */
function hasAttribute(Class_|Interface_|ClassMethod $attributeTarget, string $attribute): bool
{
    return any(
        fn(string $classAttribute) => $classAttribute === $attribute,
        getAttributes($attributeTarget)
    );
}

/**
 * @return list<class-string>
 */
function getAttributes(Class_|Interface_|ClassMethod $attributeTarget): array
{
    return toArray(
        flatMap(
            fn(AttributeGroup $group) => map(
                fn(Attribute $attribute) => $attribute->name->toString(),
                $group->attrs
            ),
            $attributeTarget->attrGroups
        )
    );
}
