<?php

namespace Structure\Service\Structure;

use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Fichier\Service\Fichier\FichierStorageService;
use Application\Service\Source\SourceService;
use Structure\Service\UniteRecherche\UniteRechercheService;
use Application\SourceCodeStringHelper;
use Import\Service\SynchroService;
use Interop\Container\ContainerInterface;

/**
 * @author Unicaen
 */
class StructureServiceFactory
{
    /**
     * @param ContainerInterface $container
     * @return StructureService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var SourceService $sourceService
         * @var SynchroService $synchroService
         * @var EcoleDoctoraleService $ecoleService
         * @var EtablissementService $etablissementService
         * @var UniteRechercheService $uniteRechercheService
         */
        $sourceService = $container->get(SourceService::class);
        $synchroService = $container->get(SynchroService::class);
        $ecoleService = $container->get(EcoleDoctoraleService::class);
        $etablissementService = $container->get(EtablissementService::class);
        $uniteRechercheService = $container->get(UniteRechercheService::class);

        /** @var \Fichier\Service\Fichier\FichierStorageService $fileService */
        $fileService = $container->get(FichierStorageService::class);

        $service = new StructureService;
        $service->setSourceService($sourceService);
        $service->setSynchroService($synchroService);
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