<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model\Cronjob;

use Renttek\Attributes\Model\ConfigGenerator;

/**
 * @psalm-type Cronjob = array{instance: class-string, name: string, disabled: bool, shared: bool, schedule?: string, config_path?: string}
 */
class CronjobConfig
{

    private array $cronjobs = [];
    private bool $initialized = false;

    public function __construct(
        private readonly ConfigGenerator $configGenerator
    ) {
    }

    /**
     * @param class-string $instance
     */
    public function addCronjob(
        string $instance,
        string $method,
        string $name,
        string $group,
        ?string $schedule,
        ?string $configPath,
    ): void {
        $this->cronjobs[$group] ??= [];

        $this->cronjobs[$group][$name] = [
            'name'     => $name,
            'instance' => $instance,
            'method'   => $method,
        ];

        if ($schedule !== null) {
            $this->cronjobs[$group][$name]['schedule'] = $schedule;
        }

        if ($configPath !== null) {
            $this->cronjobs[$group][$name]['config_path'] = $configPath;
        }
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
     * @return array<string, array<string, Cronjob>>
     */
    public function getJobs(): array
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->cronjobs;
    }
}
