<?php

namespace Application\Controller\Factory\Rapport;

use Application\Controller\Rapport\RapportCsiRechercheController;
use Application\Entity\Db\TypeRapport;
use Fichier\Service\Fichier\FichierService;
use Application\Service\Rapport\RapportSearchService;
use Application\Service\Rapport\RapportService;
use Interop\Container\ContainerInterface;

class RapportCsiRechercheControllerFactory
{
    public function __invoke(ContainerInterface $container): RapportCsiRechercheController
    {
        /**
         * @var RapportSearchService $searchService
         * @var FichierService $fichierService
         */
        $searchService = $container->get(RapportSearchService::class);
        $fichierService = $container->get(FichierService::class);

        $controller = new RapportCsiRechercheController();
        $controller->setSearchService($searchService);
        $controller->setFichierService($fichierService);

        /**
         * @var RapportService $rapportService
         */
        $rapportService = $container->get(RapportService::class);
        $typeRapport = $rapportService->findTypeRapportByCode(TypeRapport::RAPPORT_CSI);
        $searchService->setTypeRapport($typeRapport);
        $controller->setTypeRapport($typeRapport);

        return $controller;
    }
}