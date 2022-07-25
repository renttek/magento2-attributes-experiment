<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model\Cronjob;

use Renttek\Attributes\Model\AttributeConfigInterface;
use Renttek\Attributes\Model\ConfigGenerator;

/**
 * @psalm-type Cronjob = array{instance: class-string, name: string, disabled: bool, shared: bool, schedule?: string, config_path?: string}
 */
class CronjobConfig implements AttributeConfigInterface
{
    private const ID = 'cronjob';

    /**
     * @var array<string, array<string, Cronjob>>
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
     * @return array<string, array<string, Cronjob>>
     */
    public function getConfig(): array
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->config;
    }

    private function initialize(): void
    {
        foreach ($this->configGenerator->generate() as $cronjobConfig) {
            $this->addCronjob(
                $cronjobConfig['instance'],
                $cronjobConfig['method'],
                $cronjobConfig['name'],
                $cronjobConfig['group'],
                $cronjobConfig['schedule'],
                $cronjobConfig['configPath'],
            );
        }

        $this->initialized = true;
    }

    /**
     * @param class-string $instance
     */
    private function addCronjob(
        string $instance,
        string $method,
        string $name,
        string $group,
        ?string $schedule,
        ?string $configPath,
    ): void {
        $this->config[$group] ??= [];

        $this->config[$group][$name] = [
            'name'     => $name,
            'instance' => $instance,
            'method'   => $method,
        ];

        if ($schedule !== null) {
            $this->config[$group][$name]['schedule'] = $schedule;
        }

        if ($configPath !== null) {
            $this->config[$group][$name]['config_path'] = $configPath;
        }
    }
}
