<?php

namespace Structure\Service\Structure;

use Application\Service\Source\SourceService;
use Application\SourceCodeStringHelper;
use Fichier\Service\Fichier\FichierStorageService;
use Interop\Container\ContainerInterface;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\UniteRecherche\UniteRechercheService;

class StructureServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): StructureService
    {
        /**
         * @var SourceService $sourceService
         * @var EcoleDoctoraleService $ecoleService
         * @var EtablissementService $etablissementService
         * @var UniteRechercheService $uniteRechercheService
         */
        $sourceService = $container->get(SourceService::class);
        $ecoleService = $container->get(EcoleDoctoraleService::class);
        $etablissementService = $container->get(EtablissementService::class);
        $uniteRechercheService = $container->get(UniteRechercheService::class);

        /** @var \Fichier\Service\Fichier\FichierStorageService $fileService */
        $fileService = $container->get(FichierStorageService::class);

        $service = new StructureService;
        $service->setSourceService($sourceService);
        $service->setEcoleDoctoraleService($ecoleService);
        $service->setEtablissementService($etablissementService);
        $service->setUniteRechercheService($uniteRechercheService);
        $service->setFichierStorageService($fileService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}