<?php

namespace Feature;

use Feature\Entity\FeatureInterface;
use Webmozart\Assert\Assert;

/**
 * Trait fournissant de quoi activer/désactiver des fonctionnalités de type {@see \Feature\Entity\FeatureInterface}.
 */
trait FeatureAwareTrait
{
    /**
     * @var array<string, FeatureInterface>
     */
    protected array $features = [];

    public function hasFeature(FeatureInterface|string $feature): bool
    {
        if ($feature instanceof FeatureInterface) {
            $key = $feature->getCode();
        } else {
            $key = $feature;
        }
        return array_key_exists($key, $this->features);
    }

    public function addFeature(FeatureInterface $feature): static
    {
        $this->features[$feature->getCode()] = $feature;
        return $this;
    }

    public function removeFeature(FeatureInterface|string $feature): static
    {
        if ($feature instanceof FeatureInterface) {
            $key = $feature->getCode();
        } else {
            $key = $feature;
        }
        unset($this->features[$key]);
        return $this;
    }

    public function getFeature(string $name): FeatureInterface
    {
        Assert::keyExists($this->features, $name, "Feature introuvable !");
        return $this->features[$name];
    }
}