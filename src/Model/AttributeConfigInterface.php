<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model;

interface AttributeConfigInterface
{
    public function getId(): string;
    public function getConfig(): array;
}
