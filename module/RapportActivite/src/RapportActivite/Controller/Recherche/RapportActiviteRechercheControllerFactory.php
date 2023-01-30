<?php

namespace RapportActivite\Controller\Recherche;

use Application\Entity\Db\TypeValidation;
use Application\Service\Validation\ValidationService;
use Fichier\Service\Fichier\FichierService;
use Psr\Container\ContainerInterface;
use RapportActivite\Rule\Avis\RapportActiviteAvisRule;
use RapportActivite\Rule\Operation\RapportActiviteOperationRule;
use RapportActivite\Service\Fichier\RapportActiviteFichierService;
use RapportActivite\Service\RapportActiviteService;
use RapportActivite\Service\Search\RapportActiviteSearchService;

class RapportActiviteRechercheControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteRechercheController
    {
        /** @var ValidationService $validationService */
        $validationService = $container->get(ValidationService::class);
        $typeValidation = $validationService->findTypeValidationByCode(TypeValidation::CODE_RAPPORT_ACTIVITE_AUTO);

        /** @var RapportActiviteSearchService $searchService */
        $searchService = $container->get(RapportActiviteSearchService::class);
        $searchService->setTypeValidation($typeValidation);

        /** @var FichierService $fichierService */
        $fichierService = $container->get(FichierService::class);

        $controller = new RapportActiviteRechercheController();
        $controller->setSearchService($searchService);
        $controller->setFichierService($fichierService);
        $controller->setTypeValidation($typeValidation);

        /** @var RapportActiviteService $rapportActiviteService */
        $rapportActiviteService = $container->get(RapportActiviteService::class);
        $controller->setRapportActiviteService($rapportActiviteService);

        /** @var RapportActiviteFichierService $rapportActiviteFichierService */
        $rapportActiviteFichierService = $container->get(RapportActiviteFichierService::class);
        $controller->setRapportActiviteFichierService($rapportActiviteFichierService);

        /** @var \RapportActivite\Rule\Avis\RapportActiviteAvisRule $rapportActiviteAvisRule */
        $rapportActiviteAvisRule = $container->get(RapportActiviteAvisRule::class);
        $controller->setRapportActiviteAvisRule($rapportActiviteAvisRule);

        /** @var \RapportActivite\Rule\Operation\RapportActiviteOperationRule $rapportActiviteOperationRule */
        $rapportActiviteOperationRule = $container->get(RapportActiviteOperationRule::class);
        $controller->setRapportActiviteOperationRule($rapportActiviteOperationRule);

        return $controller;
    }
}