<?php

namespace These\Service\These\Factory;

use Application\Service\DomaineScientifiqueService;
use Application\Service\Financement\FinancementService;
use Application\Service\UserContextService;
use Interop\Container\ContainerInterface;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use Structure\Service\UniteRecherche\UniteRechercheService;
use These\Service\These\TheseSearchService;
use These\Service\These\TheseService;
use These\Service\TheseAnneeUniv\TheseAnneeUnivService;

class TheseSearchServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return TheseSearchService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var TheseService $theseService
         * @var UserContextService $userContextService
         * @var EtablissementService $etablissementService
         * @var UniteRechercheService $uniteService
         * @var EcoleDoctoraleService $ecoleDoctoraleService
         * @var StructureService $structureService
         * @var DomaineScientifiqueService $domaineService
         * @var FinancementService $financementService
         * @var TheseAnneeUnivService $theseAnneeUnivService
         */
        $theseService = $container->get('TheseService');
        $userContextService = $container->get('UserContextService');
        $ecoleDoctoraleService = $container->get('EcoleDoctoraleService');
        $uniteService = $container->get('UniteRechercheService');
        $etablissementService = $container->get('EtablissementService');
        $structureService = $container->get(StructureService::class);
        $domaineService = $container->get(DomaineScientifiqueService::class);
        $financementService = $container->get(FinancementService::class);
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);

        $service = new TheseSearchService();
        $service->setTheseService($theseService);
        $service->setUserContextService($userContextService);
        $service->setEtablissementService($etablissementService);
        $service->setUniteRechercheService($uniteService);
        $service->setEcoleDoctoraleService($ecoleDoctoraleService);
        $service->setStructureService($structureService);
        $service->setDomaineScientifiqueService($domaineService);
        $service->setFinancementService($financementService);
        $service->setAnneesUnivs($theseAnneeUnivService);

        return $service;
    }
}
