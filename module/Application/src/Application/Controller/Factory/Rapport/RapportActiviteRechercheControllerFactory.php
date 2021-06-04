<?php

namespace Application\Controller\Factory\Rapport;

use Application\Controller\Rapport\RapportActiviteRechercheController;
use Application\Entity\Db\TypeRapport;
use Application\Entity\Db\TypeValidation;
use Application\Service\Fichier\FichierService;
use Application\Service\Rapport\RapportSearchService;
use Application\Service\Rapport\RapportService;
use Application\Service\Validation\ValidationService;
use Interop\Container\ContainerInterface;

class RapportActiviteRechercheControllerFactory
{
    public function __invoke(ContainerInterface $container): RapportActiviteRechercheController
    {
        $rapportService = $container->get(RapportService::class);
        $typeRapport = $rapportService->findTypeRapportByCode(TypeRapport::RAPPORT_ACTIVITE);

        $validationService = $container->get(ValidationService::class);
        $typeValidation = $validationService->findTypeValidationByCode(TypeValidation::CODE_RAPPORT_ACTIVITE);

        $searchService = $container->get(RapportSearchService::class);
        $searchService->setTypeRapport($typeRapport);
        $searchService->setTypeValidation($typeValidation);

        $fichierService = $container->get(FichierService::class);

        $controller = new RapportActiviteRechercheController();
        $controller->setSearchService($searchService);
        $controller->setFichierService($fichierService);
        $controller->setTypeRapport($typeRapport);
        $controller->setTypeValidation($typeValidation);

        return $controller;
    }
}