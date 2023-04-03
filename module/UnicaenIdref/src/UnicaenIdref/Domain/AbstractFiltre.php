<?php

namespace UnicaenIdref\Domain;

abstract class AbstractFiltre
{
    protected const VALUE_Oui = 'Oui';
    protected const VALUE_Non = 'Non';

    protected string $filtre;
    protected string $filtreValue;

    /**
     * @return string
     */
    public function getFiltre(): string
    {
        return $this->filtre;
    }

    /**
     * @param string $filtre
     * @return self
     */
    protected function setFiltre(string $filtre): self
    {
        $this->filtre = $filtre;
        return $this;
    }

    /**
     * @return string
     */
    public function getFiltreValue(): string
    {
        return $this->filtreValue;
    }

    /**
     * @param string $filtreValue
     * @return self
     */
    protected function setFiltreValue(string $filtreValue): self
    {
        $this->filtreValue = $filtreValue;
        return $this;
    }
}