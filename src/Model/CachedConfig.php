<?php

declare(strict_types=1);

namespace Renttek\Attributes\Model;

class CachedConfig implements AttributeConfigInterface
{
    public function __construct(
        private readonly AttributeConfigInterface $config,
        private readonly ConfigurationCache $configurationCache,
    ) {
    }

    public function getId(): string
    {
        return $this->config->getId();
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $config = $this->configurationCache->load($this->getId());

        if ($config !== null) {
            return $config;
        }

        $config = $this->config->getConfig();

        $this->configurationCache->save($this->getId(), $config);

        return $config;
    }
}
