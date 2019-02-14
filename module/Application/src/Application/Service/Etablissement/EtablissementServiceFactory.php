<?php

namespace Application\Service\Etablissement;

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
        $service = new EtablissementService();

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $serviceLocator->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}
