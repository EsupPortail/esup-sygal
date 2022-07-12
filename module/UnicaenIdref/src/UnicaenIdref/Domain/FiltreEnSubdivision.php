<?php

namespace UnicaenIdref\Domain;

class FiltreEnSubdivision extends AbstractFiltre
{
    protected string $filtre = 'En subdivision';

    public function setOui(): self
    {
        return $this->setFiltreValue(AbstractFiltre::VALUE_Oui);
    }

    public function setNon(): self
    {
        return $this->setFiltreValue(AbstractFiltre::VALUE_Non);
    }
}