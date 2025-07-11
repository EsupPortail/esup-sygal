<?php

namespace Formation\Controller\Recherche;

use Application\Export\Xlsx\Manager\XlsxExportManager;
use Formation\Service\Formation\Search\FormationSearchService;
use Psr\Container\ContainerInterface;
use UnicaenParametre\Service\Parametre\ParametreService;
use UnicaenSearch\SearchServicePluginManager;

class FormationRechercheControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FormationRechercheController
    {
        $controller = new FormationRechercheController();

        /** @var FormationSearchService $searchService */
        $searchService = $container->get(SearchServicePluginManager::class)->get(FormationSearchService::class);
        $controller->setSearchService($searchService);

        /** @var ParametreService $parametreService */
        $parametreService = $container->get(ParametreService::class);
        $controller->setParametreService($parametreService);

        /** @var XlsxExportManager $formationXlsxExportManager */
        $formationXlsxExportManager = $container->get(XlsxExportManager::class);
        $controller->setFormationXlsxExportManager($formationXlsxExportManager);

        return $controller;
    }
}