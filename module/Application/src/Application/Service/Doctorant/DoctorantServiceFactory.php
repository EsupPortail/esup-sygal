<?php

namespace Application\Service\Doctorant;

use Application\Service\Etablissement\EtablissementService;
use Application\SourceCodeStringHelper;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DoctorantServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EtablissementService $etablissementService */
        $etablissementService = $serviceLocator->get('EtablissementService');

        $service = new DoctorantService();

        $service->setEtablissementService($etablissementService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $serviceLocator->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}