<?php

namespace RapportActivite\Controller\Recherche;

use Application\Entity\Db\TypeValidation;
use Application\Service\Fichier\FichierService;
use Application\Service\Financement\FinancementService;
use Application\Service\Validation\ValidationService;
use Psr\Container\ContainerInterface;
use RapportActivite\Service\Avis\RapportActiviteAvisService;
use RapportActivite\Service\Search\RapportActiviteSearchService;
use RapportActivite\Service\RapportActiviteService;

class RapportActiviteRechercheControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteRechercheController
    {
        /** @var RapportActiviteService $rapportService */
        $rapportService = $container->get(RapportActiviteService::class);
        $typeRapport = $rapportService->findTypeRapport();

        /** @var ValidationService $validationService */
        $validationService = $container->get(ValidationService::class);
        $typeValidation = $validationService->findTypeValidationByCode(TypeValidation::CODE_RAPPORT_ACTIVITE);

        /** @var RapportActiviteSearchService $searchService */
        $searchService = $container->get(RapportActiviteSearchService::class);
        $searchService->setTypeRapport($typeRapport);
        $searchService->setTypeValidation($typeValidation);

        /** @var FichierService $fichierService */
        $fichierService = $container->get(FichierService::class);

        /** @var RapportActiviteAvisService $rapportActiviteAvisService */
        $rapportActiviteAvisService = $container->get(RapportActiviteAvisService::class);

        /** @var \Application\Service\Financement\FinancementService $financementService */
        $financementService = $container->get(FinancementService::class);

        $controller = new RapportActiviteRechercheController();
        $controller->setSearchService($searchService);
        $controller->setFichierService($fichierService);
        $controller->setRapportActiviteAvisService($rapportActiviteAvisService);
        $controller->setFinancementService($financementService);
        $controller->setTypeRapport($typeRapport);
        $controller->setTypeValidation($typeValidation);

        return $controller;
    }
}