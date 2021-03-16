<?php

namespace Application\Service\Etablissement;

use Application\Service\Fichier\FichierService;
use Application\Service\File\FileService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;

class EtablissementServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return EtablissementService
     */
    public function __invoke(ContainerInterface $container)
    {
        $service = new EtablissementService();

        /** @var FichierService $fichierService */
        $fichierService = $container->get(FichierService::class);
        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);
        $service->setFichierService($fichierService);

        return $service;
    }
}
