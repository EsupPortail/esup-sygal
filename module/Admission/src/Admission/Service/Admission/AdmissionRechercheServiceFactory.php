<?php

namespace Admission\Service\Admission;

use Application\Service\DomaineScientifiqueService;
use Application\Service\Financement\FinancementService;
use Application\Service\UserContextService;
use Interop\Container\ContainerInterface;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use Structure\Service\UniteRecherche\UniteRechercheService;
use These\Service\TheseAnneeUniv\TheseAnneeUnivService;

class AdmissionRechercheServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return AdmissionRechercheService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var AdmissionService $admissionService
         * @var UserContextService $userContextService
         * @var EtablissementService $etablissementService
         * @var UniteRechercheService $uniteService
         * @var EcoleDoctoraleService $ecoleDoctoraleService
         * @var StructureService $structureService
         */
        $admissionService = $container->get(AdmissionService::class);
        $userContextService = $container->get('UserContextService');
        $ecoleDoctoraleService = $container->get('EcoleDoctoraleService');
        $uniteService = $container->get('UniteRechercheService');
        $etablissementService = $container->get('EtablissementService');
        $structureService = $container->get(StructureService::class);

        $service = new AdmissionRechercheService();
        $service->setAdmissionService($admissionService);
        $service->setUserContextService($userContextService);
        $service->setEtablissementService($etablissementService);
        $service->setUniteRechercheService($uniteService);
        $service->setEcoleDoctoraleService($ecoleDoctoraleService);
        $service->setStructureService($structureService);

        return $service;
    }
}
