<?php

namespace Application\Service\ListeDiffusion\Address;

class ListeDiffusionAddressParserResultWithED extends ListeDiffusionAddressParserResult
{
    /**
     * Sigle de l'ED concernée, sans espace.
     *
     * @var string
     */
    protected $ecoleDoctorale;

    /**
     * @return string
     */
    public function getEcoleDoctorale(): string
    {
        return $this->ecoleDoctorale;
    }

    /**
     * @param string $ecoleDoctorale
     * @return ListeDiffusionAddressParserResultWithED
     */
    public function setEcoleDoctorale(string $ecoleDoctorale): ListeDiffusionAddressParserResultWithED
    {
        $this->ecoleDoctorale = $ecoleDoctorale;
        return $this;
    }

}