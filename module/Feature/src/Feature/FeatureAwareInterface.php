<?php

namespace Feature;

use Feature\Entity\FeatureInterface;

/**
 * Interface spécifiant de quoi activer/désactiver/obtenir des fonctionnalités
 * de type {@see \Feature\Entity\FeatureInterface}.
 */
interface FeatureAwareInterface
{
    public function hasFeature(FeatureInterface|string $feature): bool;
    public function addFeature(FeatureInterface $feature): static;
    public function removeFeature(FeatureInterface|string $feature): static;
    public function getFeature(string $name): FeatureInterface;}