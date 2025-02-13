<?php

namespace Soutenance\Service\Proposition\PropositionThese;

use Interop\Container\ContainerInterface;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use Structure\Service\UniteRecherche\UniteRechercheService;

class PropositionTheseSearchServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): PropositionTheseSearchService
    {
        /**
         * @var PropositionTheseService $propositionService
         * @var \Structure\Service\Structure\StructureService $structureService
         * @var EtablissementService $etablissementService
         * @var UniteRechercheService $uniteService
         * @var EcoleDoctoraleService $ecoleDoctoraleService
         */
        $propositionService = $container->get(PropositionTheseService::class);
        $structureService = $container->get(StructureService::class);
        $ecoleDoctoraleService = $container->get('EcoleDoctoraleService');
        $uniteService = $container->get('UniteRechercheService');
        $etablissementService = $container->get('EtablissementService');

        $service = new PropositionTheseSearchService();
        $service->setPropositionTheseService($propositionService);
        $service->setStructureService($structureService);
        $service->setEtablissementService($etablissementService);
        $service->setUniteRechercheService($uniteService);
        $service->setEcoleDoctoraleService($ecoleDoctoraleService);

        return $service;
    }
}
