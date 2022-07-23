<?php

declare(strict_types=1);

namespace Renttek\Attributes\Cron;

use Renttek\Attributes\Attributes\Cronjob;

class RepeatingStuff
{
    #[Cronjob('some_work', schedule: '*/10 * * * *')]
    public function doSomeWork(): void
    {
    }

    #[Cronjob('cleanup', group: 'cleanup', configPath: 'renttek/awesomecronjob/cleanup')]
    public function andCleanupAfterwards(): void
    {
    }
}
