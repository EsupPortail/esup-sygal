<?php

namespace Application\Service\ListeDiffusion\Address;

interface ListeDiffusionAddressParserInterface
{
    public function setAddress(string $address): self;
    public function parse(): ListeDiffusionAddressParserResult;
}