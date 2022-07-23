<?php

declare(strict_types=1);

namespace Renttek\Attributes\Plugin\Config;

use Magento\Cron\Model\ConfigInterface;
use Renttek\Attributes\Model\Cronjob\CronjobConfig;

class AddCronjobConfig
{
    public function __construct(
        private readonly CronjobConfig $cronjobConfig
    ) {
    }

    public function afterGetJobs(ConfigInterface $configModel, array $jobs): array
    {
        $x = array_replace_recursive($jobs, $this->cronjobConfig->getJobs());

        dd($x);

        return $jobs;
    }
}
