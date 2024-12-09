<?php

namespace Feature\Entity;

use Laminas\Permissions\Acl\Resource\ResourceInterface;

/**
 * Feature simple (service).
 */
class Feature implements FeatureInterface, ResourceInterface
{
    use FeatureTrait;

    private array $config = [];

    public function __construct(array $config = [])
    {
        $config = array_merge([
            'enabled' => true,
        ], $config);

        $this->setEnabled($config['enabled']);
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }
}
