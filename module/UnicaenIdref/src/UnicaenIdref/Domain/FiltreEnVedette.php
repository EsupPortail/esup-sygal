<?php

namespace UnicaenIdref\Domain;

class FiltreEnVedette extends AbstractFiltre
{
    protected string $filtre = 'En vedette';

    public function setOui(): self
    {
        return $this->setFiltreValue(AbstractFiltre::VALUE_Oui);
    }

    public function setNon(): self
    {
        return $this->setFiltreValue(AbstractFiltre::VALUE_Non);
    }
}