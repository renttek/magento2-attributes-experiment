<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model\Event;

use Magento\Framework\App\Area;
use Renttek\Attributes\Model\ConfigGenerator;

use function iter\filter;
use function iter\toArrayWithKeys;

/**
 * @psalm-type Observer = array{instance: class-string, name: string, disabled: bool, shared: bool}
 */
class ObserverConfig
{
    /**
     * @var array<string, array<string, ObserverStruct>>
     */
    private array $observers = [];
    private bool $initialized = false;

    public function __construct(
        private readonly ConfigGenerator $configGenerator
    ) {
    }

    /**
     * @param class-string $instance
     */
    public function addObserver(
        ?string $area,
        string $event,
        string $instance,
        string $name,
        bool $shared,
        bool $disabled
    ): void {
        $area ??= Area::AREA_GLOBAL;

        $this->observers[$area]                ??= [];
        $this->observers[$area][$event]        ??= [];
        $this->observers[$area][$event][$name] = [
            'instance' => $instance,
            'name'     => $name,
            'disabled' => $disabled,
            'shared'   => $shared,
        ];
    }

    private function initialize(): void
    {
        foreach ($this->configGenerator->generate() as $observerConfig) {
            $this->addObserver(
                $observerConfig['area'],
                $observerConfig['event'],
                $observerConfig['instance'],
                $observerConfig['name'],
                $observerConfig['shared'],
                $observerConfig['disabled'],
            );
        }

        $this->initialized = true;
    }

    /**
     * @psalm-param Area::AREA_* $area
     *
     * @return array<string, ObserverStruct>
     */
    public function getObserversForEvent(string $area, string $event): array
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        $observers = $this->getByAreaAndName($area, $event);

        if ($area !== Area::AREA_GLOBAL) {
            $globalObservers = $this->getByAreaAndName(Area::AREA_GLOBAL, $event);
            $observers       = array_replace_recursive($globalObservers, $observers);
        }

        return $observers;
    }

    /**
     * @psalm-param Area::AREA_* $area
     *
     * @return array<string, ObserverStruct>
     */
    private function getByAreaAndName(string $area, string $name): array
    {
        return toArrayWithKeys(
            filter(
                fn(array $observer) => $name === $observer['event'],
                $this->observers[$area] ?? []
            )
        );
    }
}
