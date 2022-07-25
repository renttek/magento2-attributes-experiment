<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model\Event;

use Magento\Framework\App\Area;
use Renttek\Attributes\Model\AttributeConfigInterface;
use Renttek\Attributes\Model\ConfigGenerator;

/**
 * @psalm-type Observer = array{instance: class-string, name: string, disabled: bool, shared: bool}
 */
class ObserverConfig implements AttributeConfigInterface
{
    private const ID = 'observers';

    /**
     * @var array<string, array<string, array<string, Observer>>>
     */
    private array $config = [];
    private bool $initialized = false;

    public function __construct(
        private readonly ConfigGenerator $configGenerator
    ) {
    }

    public function getId(): string
    {
        return self::ID;
    }

    /**
     * @return array<string, array<string, array<string, Observer>>>
     */
    public function getConfig(): array
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->config;
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

        $this->config[$area]                ??= [];
        $this->config[$area][$event]        ??= [];
        $this->config[$area][$event][$name] = [
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
}
