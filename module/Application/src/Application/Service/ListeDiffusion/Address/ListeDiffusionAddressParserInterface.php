<?php

namespace Application\Service\ListeDiffusion\Address;

interface ListeDiffusionAddressParserInterface
{
    /**
     * @param string $address
     * @return self
     */
    public function setAddress(string $address);

    /**
     * @return ListeDiffusionAddressParserResult
     */
    public function parse();
}