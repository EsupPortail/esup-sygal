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
use Application\Service\TheseAnneeUniv\TheseAnneeUnivService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\Service\UserContextService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;
use UnicaenAuth\Service\AuthorizeService;

class TheseRechercheServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return TheseRechercheService
     */
    public function __invoke(ContainerInterface $container)
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
         * @var AuthorizeService $authorizeService
         * @var TheseAnneeUnivService $theseAnneeUnivService
         */
        $theseService = $container->get('TheseService');
        $userContextService = $container->get('UserContextService');
        $ecoleDoctoraleService = $container->get('EcoleDoctoraleService');
        $uniteService = $container->get('UniteRechercheService');
        $etablissementService = $container->get('EtablissementService');
        $sourceService = $container->get('SourceService');
        $structureService = $container->get(StructureService::class);
        $domaineService = $container->get(DomaineScientifiqueService::class);
        $financementService = $container->get(FinancementService::class);
        $authorizeService = $container->get('BjyAuthorize\Service\Authorize');
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);

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
        $service->setAuthorizeService($authorizeService);
        $service->setTheseAnneeUnivService($theseAnneeUnivService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);

        return $service;
    }
}
