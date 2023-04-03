<?php

namespace UnicaenIdref\Domain;

class FiltreEnVie extends AbstractFiltre
{
    protected string $filtre = 'En vie';

    public function setOui(): self
    {
        return $this->setFiltreValue(AbstractFiltre::VALUE_Oui);
    }

    public function setNon(): self
    {
        return $this->setFiltreValue(AbstractFiltre::VALUE_Non);
    }
}