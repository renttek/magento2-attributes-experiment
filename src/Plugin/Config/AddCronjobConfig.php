<?php

declare(strict_types=1);

namespace Renttek\Attributes\Plugin\Config;

use Magento\Cron\Model\ConfigInterface;
use Renttek\Attributes\Model\AttributeConfigInterface;

class AddCronjobConfig
{
    public function __construct(
        private readonly AttributeConfigInterface $cronjobConfig
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetJobs(ConfigInterface $configModel, array $originalCronjobs): array
    {
        $cronjobs = $this->cronjobConfig->getConfig();

        return array_replace_recursive($originalCronjobs, $cronjobs);
    }
}
