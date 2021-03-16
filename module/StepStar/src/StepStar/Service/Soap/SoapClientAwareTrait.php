<?php

namespace StepStar\Service\Soap;

trait SoapClientAwareTrait
{
    /**
     * @var SoapClient
     */
    protected $soapClient;

    /**
     * @param SoapClient $service
     */
    public function setSoapClient(SoapClient $service)
    {
        $this->soapClient = $service;
    }
}