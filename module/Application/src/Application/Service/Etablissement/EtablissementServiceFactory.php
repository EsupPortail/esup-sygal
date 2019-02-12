<?php

namespace Application\Service\Etablissement;

use Application\Service\Parametre\ParametreService;
use Application\SourceCodeStringHelper;
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
        /**
         * @var ParametreService $parametreService
         */
        $parametreService = $serviceLocator->get('ParametreService');

        $service = new EtablissementService();
        $service->setParametreService($parametreService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $serviceLocator->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}
