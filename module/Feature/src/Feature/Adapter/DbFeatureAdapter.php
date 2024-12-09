<?php

namespace Feature\Adapter;

use BadMethodCallException;
use DateTime;
use Feature\Entity\Db\Feature as DbFeature;

/**
 * Classe mère abstraite des adapters pour les features existantes en bdd.
 */
abstract class DbFeatureAdapter implements DbFeatureAdapterInterface
{
    public function __construct(private readonly DbFeature $feature)
    {
    }

    public function getCode(): string
    {
        return $this->feature->getCode();
    }

    public function isEnabled(?DateTime $dateObservation = null): bool
    {
        return $this->feature->isEnabled($dateObservation);
    }

    public function setEnabled(?bool $enabled = true): void
    {
        throw new BadMethodCallException('Not supported');
    }
}