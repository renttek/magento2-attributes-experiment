<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model;

use Magento\Framework\App\State;

class CachedConfig implements AttributeConfigInterface
{
    public function __construct(
        private readonly AttributeConfigInterface $config,
        private readonly ConfigurationCache $configurationCache,
        private readonly State $state,
    ) {
    }

    public function getId(): string
    {
        return $this->config->getId();
    }

    public function getConfig(): array
    {
        $config = $this->configurationCache->load($this->getId());

        if ($config !== null) {
            return $config;
        }

        if ($this->isProduction()) {
            return [];
        }

        $config = $this->config->getConfig();

        $this->configurationCache->save($this->getId(), $config);

        return $config;
    }

    private function isProduction(): bool
    {
        return $this->state->getMode() === State::MODE_PRODUCTION;
    }
}
