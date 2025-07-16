<?php

namespace Feature\Entity;

use DateTime;

trait FeatureTrait
{
    public const RESOURCE_ID = 'Feature';

    private string $code;
    private string $libelle;
    private ?DateTime $enabledStartDate = null;
    private ?DateTime $enabledEndDate = null;

    public function getCode(): string
    {
        return $this->code;
    }

    public function getEnabledStartDate(): ?DateTime
    {
        return $this->enabledStartDate;
    }

    public function setEnabledStartDate(?DateTime $enabledStartDate): self
    {
        $this->enabledStartDate = $enabledStartDate;
        return $this;
    }

    public function getEnabledEndDate(): ?DateTime
    {
        return $this->enabledEndDate;
    }

    public function setEnabledEndDate(?DateTime $enabledEndDate): self
    {
        $this->enabledEndDate = $enabledEndDate;
        return $this;
    }

    public function setEnabled(?bool $enabled = true): void
    {
        $this->setEnabledStartDate($enabled ? new DateTime() : null);
    }

    /**
     * Teste si la feature est activée ou non.
     *
     * Possibilité de tester si la feature était ou sera activée à la date d'observation spécifiée.
     */
    public function isEnabled(?DateTime $dateObservation = null): bool
    {
        // pas de date de début d'activation = pas activée
        if ($this->enabledStartDate === null) {
            return false;
        }

        // mise à 0 des h, min, s avant les comparaisons
        $dateObserv = $dateObservation ? (clone $dateObservation)->setTime(0, 0) : (new DateTime())->setTime(0, 0);
        $enabledStartDate = (clone $this->enabledStartDate)->setTime(0, 0);
        $enabledEndDate = $this->enabledEndDate ? (clone $this->enabledEndDate)->setTime(0, 0) : null;

        // si la date de début d'activation n'est pas encore dépassée : feature pas activée
        if ($enabledStartDate > $dateObserv) {
            return false;
        }
        // si une date de fin d'activation a été spécifiée et qu'elle est dépassée : feature plus activée
        if ($enabledEndDate && $enabledEndDate < $dateObserv) {
            return false;
        }

        return true;
    }

    public function getLibelle(): string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;
        return $this;
    }


    public function getResourceId(): string
    {
        return self::RESOURCE_ID;
    }
}
