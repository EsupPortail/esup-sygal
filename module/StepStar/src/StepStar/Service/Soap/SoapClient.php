<?php

namespace StepStar\Service\Soap;

use Laminas\Soap\Client;

/**
 * Client SOAP capable d'appeler le Web Service DepotTEF de l'ABES.
 *
 * @author Unicaen
 */
class SoapClient extends Client
{
    const SOAP_VERSION = SOAP_1_1;

    protected array $params;

    public function __construct(array $params = [])
    {
        if (null !== $params) {
            $this->setParams($params);
        }

        $wsdlOptions = $this->params['wsdl'];
        $soapOptions = $this->params['soap'];

        $wsdl = $wsdlOptions['url'];

        $options = [
            'soap_version' => $soapOptions['version'] ?? self::SOAP_VERSION,
            'cache_wsdl'   => $soapOptions['cache_wsdl'] ?? 0,
            'keep_alive'   => $soapOptions['keep_alive'] ?? false,
            'proxy_host'   => $soapOptions['proxy_host'] ?? null,
            'proxy_port'   => $soapOptions['proxy_port'] ?? null,
        ];

        return parent::__construct($wsdl, $options);
    }

    public function setParams(array $params): self
    {
        $this->params = $params;

        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}