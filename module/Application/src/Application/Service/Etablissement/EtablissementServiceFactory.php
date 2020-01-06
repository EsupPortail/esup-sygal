<?php

namespace Application\Service\Etablissement;

use Application\SourceCodeStringHelper;
use UnicaenApp\Exception\RuntimeException;
use Zend\ServiceManager\ServiceLocatorInterface;

class EtablissementServiceFactory
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return EtablissementService
     */
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /** @var array $config */
        $config = $serviceLocator->get('config');

        $service = new EtablissementService();

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $serviceLocator->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);
        $service->setEtablissementPrincipalSourceCode($this->getEtablissementPrincipalSourceCodeFromConfig($config));

        return $service;
    }

    /**
     * @param array $config
     * @return string
     */
    private function getEtablissementPrincipalSourceCodeFromConfig(array $config)
    {
        $key = 'etablissement_principal_source_code';

        if (! isset($config['sygal'][$key])) {
            throw new RuntimeException(
                "Anomalie: le paramètre de config ['sygal'][$key] est introuvable.");
        }

        return $config['sygal'][$key];
    }
}
