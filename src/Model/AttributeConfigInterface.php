<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model;

interface AttributeConfigInterface
{
    public function getId(): string;

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array;
}
