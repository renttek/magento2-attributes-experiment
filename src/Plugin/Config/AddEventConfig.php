<?php

declare(strict_types=1);

namespace Renttek\Attributes\Plugin\Config;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Event\ConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Renttek\Attributes\Model\AttributeConfigInterface;

use function iter\filter;
use function iter\toArrayWithKeys;

class AddEventConfig
{
    public function __construct(
        private readonly AttributeConfigInterface $observerConfig,
        private readonly State $state,
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetObservers(ConfigInterface $configModel, array $originalObservers, string $eventName): array
    {
        try {
            $areaCode = $this->state->getAreaCode();
        } catch (LocalizedException) {
            $areaCode = Area::AREA_GLOBAL;
        }

        $observerConfig = $this->observerConfig->getConfig();
        $observers      = $this->getObserversForEvent($observerConfig, $areaCode, $eventName);

        return array_replace_recursive($originalObservers, $observers);
    }

    /**
     * @psalm-param Area::AREA_* $area
     *
     * @return array<string, ObserverStruct>
     */
    public function getObserversForEvent(array $config, string $area, string $event): array
    {
        $observers = $this->getByAreaAndName($config, $area, $event);

        if ($area !== Area::AREA_GLOBAL) {
            $globalObservers = $this->getByAreaAndName($config, Area::AREA_GLOBAL, $event);
            $observers       = array_replace_recursive($globalObservers, $observers);
        }

        return $observers;
    }

    /**
     * @psalm-param Area::AREA_* $area
     *
     * @return array<string, ObserverStruct>
     */
    private function getByAreaAndName(array $config, string $area, string $name): array
    {
        return toArrayWithKeys(
            filter(
                fn(array $observer) => $name === $observer['event'],
                $config[$area] ?? []
            )
        );
    }
}
