<?php

namespace Feature\Adapter;

use Feature\Entity\Db\Feature as DbFeature;
use Feature\Entity\FeatureInterface;

/**
 * Interface des adapters de features existantes en bdd.
 *
 * Une adapter de ce type permet d'envelopper une feature pour mieux l'identifier de par
 * sa classe au nom explicite et d'implémenter des méthodes spécifiques si besoin.
 */
interface DbFeatureAdapterInterface extends FeatureInterface
{
    public function __construct(DbFeature $feature);
}