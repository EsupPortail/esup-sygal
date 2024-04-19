<?php

namespace Application\Service\ListeDiffusion\Address;

class ListeDiffusionAddressParserResultWithED extends ListeDiffusionAddressParserResult
{
    /**
     * Numéro/code de l'ED concernée, sans espace.
     */
    protected string $ecoleDoctorale;

    /**
     * Retourne le Numéro/code de l'ED concernée, sans espace.
     */
    public function getEcoleDoctorale(): string
    {
        return $this->ecoleDoctorale;
    }

    /**
     * Spécifie le Numéro/code de l'ED concernée, sans espace.
     */
    public function setEcoleDoctorale(string $ecoleDoctorale): self
    {
        $this->ecoleDoctorale = $ecoleDoctorale;
        return $this;
    }

}