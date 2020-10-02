<?php

namespace Application\Service\ListeDiffusion\Address;

abstract class ListeDiffusionAbstractAddressParser implements ListeDiffusionAddressParserInterface
{
    /**
     * Adresse complète de la liste de diffusion,
     * ex : "ED591NBISE.doctorants.insa@normandie-univ.fr"
     *
     * @var string
     */
    protected $address;

    /**
     * @var string[]
     */
    protected $adresseElements;

    /**
     * @param string $address
     * @return self
     */
    public function setAddress(string $address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return ListeDiffusionAddressParserResult
     */
    abstract public function parse();
}