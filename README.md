# Proof of Concept: using PHP attributes in Magento 2

Please use this **ONLY** on local develop instances. **DO NOT** use this on a real shop instance!

This module contains a proof of concept for implementing some magento 2 configuration using PHP attributes.
The following configurations are currently supported:
- webapi
- cronjobs
- observers

A lot of things that are still missing: any kind of test, static code checks, caching and many other things.

## Supported configurations

Before you can use this module, you have to 'register' your module, so that it will be checked for attributes.
To do this, simply add your module name to `Renttek\Attributes\Model\ClassFinder->modules` like so:

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <type name="Renttek\Attributes\Model\ClassFinder">
        <arguments>
            <argument name="modules" xsi:type="array">
                <item name="vendor-module" xsi:type="string">Vendor_Module</item>
            </argument>
        </arguments>
    </type>
</config>
```

### Webapi

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

### Cronjobs

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


### Observers

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

## Caching

Each config will get saved to a cache file under `<magento>/var/attributes/<id>.php`


