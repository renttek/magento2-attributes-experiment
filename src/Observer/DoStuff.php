<?php

declare(strict_types=1);

namespace Renttek\Attributes\Observer;

use Renttek\Attributes\Attributes\EventSubscriber;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

#[EventSubscriber('example', area: 'frontend')]
#[EventSubscriber('example', area: 'adminhtml', disabled: true)]
#[EventSubscriber('example', area: 'webapi_rest', shared: true)]
#[EventSubscriber('example', area: 'crontab', name: 'my cron observer')]
class DoStuff implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        // TODO: Implement execute() method.
    }
}
