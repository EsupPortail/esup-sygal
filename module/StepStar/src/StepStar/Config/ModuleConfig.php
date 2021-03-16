<?php

namespace StepStar\Config;

class ModuleConfig
{
    protected $array;

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    public function getXslConfig(): array
    {
        return $this->array['xsl'];
    }

    public function getApiConfig(): array
    {
        return $this->array['api'];
    }

    public function getSoapClientConfig(): array
    {
        return $this->array['soap_client'];
    }
}