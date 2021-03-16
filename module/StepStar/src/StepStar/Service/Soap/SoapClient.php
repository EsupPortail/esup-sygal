<?php

namespace StepStar\Service\Soap;

use Traversable;
use Zend\Soap\Client;
use Zend\Stdlib\ArrayUtils;

/**
 * Client SOAP capable d'appeler le Web Service DepotTEF de l'ABES.
 *
 * @author Unicaen
 */
class SoapClient extends Client
{
    const SOAP_VERSION = SOAP_1_1;

    /**
     *
     * @var array $params
     */
    protected $params;

    /**
     * SoapClient constructor.
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        if (null !== $params) {
            $this->setParams($params);
        }

        $wsdl = $this->params['wsdl'];
        $soapOptions = $this->params['soap'];

        $options = [
            'soap_version' => $soapOptions['version'] ?? self::SOAP_VERSION,
            'cache_wsdl'   => $soapOptions['cache_wsdl'] ?? 0,
            'proxy_host'   => $soapOptions['proxy_host'] ?? null,
            'proxy_port'   => $soapOptions['proxy_port'] ?? null,
        ];

        return parent::__construct($wsdl, $options);
    }

    /**
     * @param array $params
     * @return self
     */
    public function setParams(array $params): self
    {
        if ($params instanceof Traversable) {
            $params = ArrayUtils::iteratorToArray($params);
        }

        $this->params = $params;

        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
}