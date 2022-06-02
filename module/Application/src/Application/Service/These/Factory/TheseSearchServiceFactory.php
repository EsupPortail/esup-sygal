<?php

namespace Application\Service\These\Factory;

use Application\Service\DomaineScientifiqueService;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Financement\FinancementService;
use Application\Service\Structure\StructureService;
use Application\Service\These\TheseSearchService;
use Application\Service\These\TheseService;
use Application\Service\TheseAnneeUniv\TheseAnneeUnivService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\Service\UserContextService;
use Interop\Container\ContainerInterface;

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
