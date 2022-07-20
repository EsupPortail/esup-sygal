<?php

namespace Application\Controller\Factory\Rapport;

use Application\Controller\Rapport\RapportMiparcoursRechercheController;
use Application\Entity\Db\TypeRapport;
use Fichier\Service\Fichier\FichierService;
use Application\Service\Rapport\RapportSearchService;
use Application\Service\Rapport\RapportService;
use Interop\Container\ContainerInterface;

class RapportMiparcoursRechercheControllerFactory
{
    public function __invoke(ContainerInterface $container): RapportMiparcoursRechercheController
    {
        /**
         * @var RapportSearchService $searchService
         * @var FichierService $fichierService
         */
        $searchService = $container->get(RapportSearchService::class);
        $fichierService = $container->get(FichierService::class);

        $controller = new RapportMiparcoursRechercheController();
        $controller->setSearchService($searchService);
        $controller->setFichierService($fichierService);

        /**
         * @var RapportService $rapportService
         */
        $rapportService = $container->get(RapportService::class);
        $typeRapport = $rapportService->findTypeRapportByCode(TypeRapport::RAPPORT_MIPARCOURS);
        $searchService->setTypeRapport($typeRapport);
        $controller->setTypeRapport($typeRapport);

        return $controller;
    }
}