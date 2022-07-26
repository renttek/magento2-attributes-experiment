<?php

declare(strict_types=1);

namespace Renttek\Attributes\Api;

use Renttek\Attributes\Attributes\Webapi\Resource;
use Renttek\Attributes\Attributes\Webapi\Route;
use Renttek\Attributes\Attributes\Webapi;

#[Webapi(
    path: '/V1/test',
    resources: [
        new Resource('anonymous'),
    ]
)]
interface FooRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return void
     */
    #[Route(
        path: '/:id',
        method: 'GET'
    )]
    public function get(int $id): void;
}
