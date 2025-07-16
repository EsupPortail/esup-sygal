<?php

namespace Feature\Entity\Db;

use Feature\Entity\FeatureInterface;
use Feature\Entity\FeatureTrait;
use Laminas\Permissions\Acl\Resource\ResourceInterface;

/**
 * Feature existante en base de données.
 */
class Feature implements FeatureInterface, ResourceInterface
{
    use FeatureTrait;

    private ?string $config = null;

    public function __construct(string $code, array $config = [])
    {
        $this->code = $code;

        $config = array_merge([
            'enabled' => true,
        ], $config);

        $this->setEnabled($config['enabled']);
    }

    public function getConfig(): ?string
    {
        return $this->config;
    }

    public function setConfig(?string $config): self
    {
        $this->config = $config;
        return $this;
    }
}
