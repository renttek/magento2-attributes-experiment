<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model\Webapi;

use Magento\Webapi\Model\Config\Converter;
use Renttek\Attributes\Model\ConfigGenerator;
use function array_merge as merge;

/**
 * @psalm-import-type RouteConfigs from WebapiProcessor
 * @psalm-import-type ServiceConfigs from WebapiProcessor
 * @psalm-type WebapiConfig = array{services: ServiceConfigs, routes: RouteConfigs}
 */
class WebapiConfig
{
    /**
     * @var WebapiConfig
     */
    private array $config;
    private bool $initialized = false;

    public function __construct(
        private readonly ConfigGenerator $configGenerator
    ) {
    }

    /**
     * @param RouteConfigs $routesConfig
     */
    public function addRoutes(array $routesConfig): void
    {
        $this->config[Converter::KEY_ROUTES] = merge(
            $this->config[Converter::KEY_ROUTES] ?? [],
            $routesConfig
        );
    }

    /**
     * @param ServiceConfigs $servicesConfig
     */
    public function addServices(array $servicesConfig): void
    {
        $this->config[Converter::KEY_SERVICES] = merge(
            $this->config[Converter::KEY_SERVICES] ?? [],
            $servicesConfig
        );
    }

    private function initialize(): void
    {
        foreach ($this->configGenerator->generate() as $webapiConfig) {
            $this->addRoutes($webapiConfig['routes']);
            $this->addServices($webapiConfig['services']);
        }

        $this->initialized = true;
    }

    /**
     * @return WebapiConfig
     */
    public function getConfig(): array
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return $this->config;
    }
}
