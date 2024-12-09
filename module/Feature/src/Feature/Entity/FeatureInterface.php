<?php

namespace Feature\Entity;

use DateTime;

/**
 * Interface des "features" (fonctionnalités).
 */
interface FeatureInterface
{
    /**
     * Retourne le code unique de cette feature.
     */
    public function getCode(): string;

    /**
     * Activation ou non de la feature.
     *
     * NB : une feature activée (enabled = true) peut être inactivée par des règles métier ;
     *      en revanche, une feature désactivée l'est de façon incontournable.
     */
    public function setEnabled(?bool $enabled = true): void;

    /**
     * La feature est-elle activée ou non ?
     *
     *  NB : notez qu'une feature activée (enabled = true) peut être inactivée par des règles métier ;
     *       en revanche, une feature désactivée doit l'être de façon incontournable.
     */
    public function isEnabled(?DateTime $dateObservation = null): bool;
}
