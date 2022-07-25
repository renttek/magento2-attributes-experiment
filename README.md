# Proof of Concept: using PHP attributes in Magento 2

Please use this **ONLY** on local develop instances. **DO NOT** use this on a real shop instance!

This module contains a proof of concept for implementing some magento 2 configuration using PHP attributes.
The following configurations are currently supported:
- webapi
- cronjobs
- observers

A lot of things that are still missing: any kind of test, static code checks, caching and many other things.


## Webapi

```php
use Renttek\Attributes\Attributes\Webapi;

#[Webapi(
    path: '/V1/test',
    resources: [
        new Webapi\Resource('anonymous'),
    ]
)]
interface FooRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return void
     */
    #[Webapi\Route(
        path: '/:id',
        method: 'GET',
        parameters: [
            new Webapi\Parameter('cartId', '%cart_id%'),
        ], 
    )]
    public function get(int $id): void;
}
```

## Cronjobs

```php
use Renttek\Attributes\Attributes\Cronjob;

class RepeatingStuff
{
    #[Cronjob('some_work', schedule: '*/10 * * * *')]
    public function doSomeWork(): void
    {
    }

    #[Cronjob('cleanup', group: 'cleanup', configPath: 'renttek/awesomecronjob/cleanup')]
    public function andCleanupAfterwards(): void
    {
    }
}
```


## Observers

```php
use Renttek\Attributes\Attributes\EventSubscriber;

#[EventSubscriber('example', area: 'frontend')]
#[EventSubscriber('example', area: 'adminhtml', disabled: true )]
#[EventSubscriber('example', area: 'webapi_rest', shared: true)]
#[EventSubscriber('example', area: 'cron', name: 'my cron observer')]
class DoStuff implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        // TODO: Implement execute() method.
    }
}
```

