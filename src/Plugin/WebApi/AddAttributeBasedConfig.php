<?php

declare(strict_types=1);

namespace Renttek\Attributes\Plugin\WebApi;

use Magento\Webapi\Model\Config\Converter;
use Magento\Webapi\Model\ConfigInterface;
use Renttek\Attributes\Model\ConfigGenerator;

use function array_merge as merge;

class AddAttributeBasedConfig
{
    public function __construct(
        private ConfigGenerator $configGenerator
    ) {
    }

    public function afterGetServices(ConfigInterface $configModel, array $apiConfig): array
    {
        $attributeBasedConfig = $this->configGenerator->getConfig();

        $apiConfig[Converter::KEY_SERVICES] = merge(
            $apiConfig[Converter::KEY_SERVICES],
            $attributeBasedConfig[Converter::KEY_SERVICES]
        );

        $apiConfig[Converter::KEY_ROUTES] = merge(
            $apiConfig[Converter::KEY_ROUTES],
            $attributeBasedConfig[Converter::KEY_ROUTES]
        );

        return $apiConfig;
    }
}
