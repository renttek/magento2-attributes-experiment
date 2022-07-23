<?php

declare(strict_types=1);

namespace Renttek\Attributes\Plugin\Config;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Event\ConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Renttek\Attributes\Model\Event\ObserverConfig;

class AddEventConfig
{
    public function __construct(
        private readonly ObserverConfig $observerConfig,
        private readonly State $state,
    ) {
    }

    public function afterGetObservers(ConfigInterface $configModel, array $observers, string $eventName): array
    {
        try {
            $areaCode = $this->state->getAreaCode();
        } catch (LocalizedException) {
            $areaCode = Area::AREA_GLOBAL;
        }

        $observerConfig = $this->observerConfig->getObserversForEvent($areaCode, $eventName);

        return array_replace_recursive($observers, $observerConfig);
    }
}
