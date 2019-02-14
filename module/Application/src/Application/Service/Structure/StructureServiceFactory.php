<?php

namespace Application\Service\Structure;

use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\File\FileService;
use Application\Service\Source\SourceService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\SourceCodeStringHelper;
use Import\Service\SynchroService;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

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

        /** @var FileService $fileService */
        $fileService = $container->get(FileService::class);

        $service = new StructureService;
        $service->setSourceService($sourceService);
        $service->setSynchroService($synchroService);
        $service->setEcoleDoctoraleService($ecoleService);
        $service->setEtablissementService($etablissementService);
        $service->setUniteRechercheService($uniteRechercheService);
        $service->setFileService($fileService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}