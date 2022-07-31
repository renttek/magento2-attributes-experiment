<?php

declare(strict_types=1);

namespace Renttek\Attributes\Plugin\Config;

use Magento\Webapi\Model\Config\Converter;
use Magento\Webapi\Model\ConfigInterface;
use Renttek\Attributes\Model\AttributeConfigInterface;
use Renttek\Attributes\Model\Webapi\WebapiConfig;

use function array_merge as merge;

/**
 * @psalm-import-type WebapiConfigArray from WebapiConfig
 */
class AddWebapiConfig
{
    public function __construct(
        private readonly AttributeConfigInterface $webapiConfig
    ) {
    }

    /**
     * @psalm-param WebapiConfigArray $apiConfig
     *
     * @psalm-return WebapiConfigArray
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetServices(ConfigInterface $configModel, array $apiConfig): array
    {
        $attributeBasedConfig = $this->webapiConfig->getConfig();

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
