<?php

namespace InscriptionAdministrative\Controller\Recherche;

use Application\Service\AnneeUniv\AnneeUnivService;
use InscriptionAdministrative\Service\Search\InscriptionAdministrativeSearchService;
use Psr\Container\ContainerInterface;

class InscriptionAdministrativeRechercheControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): InscriptionAdministrativeRechercheController
    {
        $controller = new InscriptionAdministrativeRechercheController();

        /** @var \InscriptionAdministrative\Service\Search\InscriptionAdministrativeSearchService $searchService */
        $searchService = $container->get(InscriptionAdministrativeSearchService::class);
        $controller->setSearchService($searchService);

        /** @var \Application\Service\AnneeUniv\AnneeUnivService $anneeUnivService */
        $anneeUnivService = $container->get(AnneeUnivService::class);
        $controller->setanneeUnivService($anneeUnivService);

        return $controller;
    }
}