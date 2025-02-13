<?php

namespace Soutenance\Service\Proposition\PropositionHDR;

use Interop\Container\ContainerInterface;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use Structure\Service\UniteRecherche\UniteRechercheService;

class PropositionHDRSearchServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): PropositionHDRSearchService
    {
        /**
         * @var PropositionHDRService $propositionService
         * @var StructureService $structureService
         * @var EtablissementService $etablissementService
         * @var UniteRechercheService $uniteService
         */
        $propositionService = $container->get(PropositionHDRService::class);
        $structureService = $container->get(StructureService::class);
        $uniteService = $container->get('UniteRechercheService');
        $etablissementService = $container->get('EtablissementService');

        $service = new PropositionHDRSearchService();
        $service->setPropositionHDRService($propositionService);
        $service->setStructureService($structureService);
        $service->setEtablissementService($etablissementService);
        $service->setUniteRechercheService($uniteService);

        return $service;
    }
}
