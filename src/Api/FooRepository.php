<?php

declare(strict_types=1);

namespace Renttek\Attributes\Api;

use Magento\Framework\DataObject;

class FooRepository implements FooRepositoryInterface
{
    public function get(int $id): void
    {
        dd($id);
    }
}
