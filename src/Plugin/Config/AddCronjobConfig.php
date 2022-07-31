<?php

declare(strict_types=1);

namespace Renttek\Attributes\Plugin\Config;

use Magento\Cron\Model\ConfigInterface;
use Renttek\Attributes\Model\AttributeConfigInterface;
use Renttek\Attributes\Model\Cronjob\CronjobConfig;

/**
 * @psalm-import-type CronjobConfigArray from CronjobConfig
 */
class AddCronjobConfig
{
    public function __construct(
        private readonly AttributeConfigInterface $cronjobConfig
    ) {
    }

    /**
     * @psalm-param CronjobConfigArray $originalCronjobs
     *
     * @psalm-return CronjobConfigArray
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetJobs(ConfigInterface $configModel, array $originalCronjobs): array
    {
        $cronjobs = $this->cronjobConfig->getConfig();

        return array_replace_recursive($originalCronjobs, $cronjobs);
    }
}
