# Proof of Concept: using PHP attributes in Magento 2

Only a part of the WebApi is currently 'supported' (read: hacked together)
This allows for registration of web api routes without using a webapi.xml, only using PHP Attributes:

```php
<?php

declare(strict_types=1);

namespace Renttek\Attributes\Api;

use Renttek\Attributes\Attributes\WebApi;

#[WebApi(
    path: '/V1/test',
    resources: [
        new WebApi\Resource('anonymous'),
    ]
)]
interface FooRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return void
     */
    #[WebApi\Route(
        path: '/:id',
        method: 'GET',
        parameters: [
            new WebApi\Parameter('cartId', '%cart_id%'),
        ], 
    )]
    public function get(int $id): void;
}
```

Next Ideas:
- Subscribing to Events (`#[SubscribeTo(...)]`)
- Registering Cronjobs (`#[Cronjob(...)]`)
- ...
