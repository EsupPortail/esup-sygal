<?php

namespace HDR\Service;

use Application\Service\UserContextService;
use Interop\Container\ContainerInterface;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use Structure\Service\UniteRecherche\UniteRechercheService;

class HDRSearchServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return HDRSearchService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var HDRService $hdrService
         * @var UserContextService $userContextService
         * @var EtablissementService $etablissementService
         * @var UniteRechercheService $uniteService
         * @var EcoleDoctoraleService $ecoleDoctoraleService
         * @var StructureService $structureService
         */
        $hdrService = $container->get(HDRService::class);
        $userContextService = $container->get('UserContextService');
        $ecoleDoctoraleService = $container->get('EcoleDoctoraleService');
        $uniteService = $container->get('UniteRechercheService');
        $etablissementService = $container->get('EtablissementService');
        $structureService = $container->get(StructureService::class);

        $service = new HDRSearchService();
        $service->setHDRService($hdrService);
        $service->setUserContextService($userContextService);
        $service->setEtablissementService($etablissementService);
        $service->setUniteRechercheService($uniteService);
        $service->setEcoleDoctoraleService($ecoleDoctoraleService);
        $service->setStructureService($structureService);

        return $service;
    }
}
