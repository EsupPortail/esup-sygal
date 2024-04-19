<?php

namespace Application\Service\ListeDiffusion\Address;

abstract class ListeDiffusionAbstractAddressParser implements ListeDiffusionAddressParserInterface
{
    /**
     * Adresse complète de la liste de diffusion,
     * ex : "ED591.doctorants.insa@normandie-univ.fr"
     */
    protected string $address;

    /**
     * @var string[]
     */
    protected array $adressElements;

    /**
     * @param string $address
     * @return self
     */
    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    abstract public function parse(): ListeDiffusionAddressParserResult;
}