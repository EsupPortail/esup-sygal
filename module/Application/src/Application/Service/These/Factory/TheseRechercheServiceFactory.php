<?php

namespace Application\Service\These\Factory;

use Application\Service\DomaineScientifiqueService;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Financement\FinancementService;
use Application\Service\Source\SourceService;
use Application\Service\Structure\StructureService;
use Application\Service\These\TheseRechercheService;
use Application\Service\These\TheseService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\Service\UserContextService;
use Zend\ServiceManager\ServiceLocatorInterface;

class TheseRechercheServiceFactory
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return TheseRechercheService
     */
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /**
         * @var TheseService $theseService
         * @var UserContextService $userContextService
         * @var EtablissementService $etablissementService
         * @var UniteRechercheService $uniteService
         * @var EcoleDoctoraleService $ecoleDoctoraleService
         * @var SourceService $sourceService
         * @var StructureService $structureService
         * @var DomaineScientifiqueService $domaineService
         * @var FinancementService $financementService
         */
        $theseService = $serviceLocator->get('TheseService');
        $userContextService = $serviceLocator->get('UserContextService');
        $ecoleDoctoraleService = $serviceLocator->get('EcoleDoctoraleService');
        $uniteService = $serviceLocator->get('UniteRechercheService');
        $etablissementService = $serviceLocator->get('EtablissementService');
        $sourceService = $serviceLocator->get('SourceService');
        $structureService = $serviceLocator->get(StructureService::class);
        $domaineService = $serviceLocator->get(DomaineScientifiqueService::class);
        $financementService = $serviceLocator->get(FinancementService::class);

        $service = new TheseRechercheService();
        $service->setTheseService($theseService);
        $service->setUserContextService($userContextService);
        $service->setEtablissementService($etablissementService);
        $service->setUniteRechercheService($uniteService);
        $service->setEcoleDoctoraleService($ecoleDoctoraleService);
        $service->setSourceService($sourceService);
        $service->setStructureService($structureService);
        $service->setDomaineScientifiqueService($domaineService);
        $service->setFinancementService($financementService);

        return $service;
    }
}
