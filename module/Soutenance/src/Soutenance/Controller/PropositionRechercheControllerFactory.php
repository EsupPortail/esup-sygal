<?php

namespace Soutenance\Controller;

use Interop\Container\ContainerInterface;
use Soutenance\Service\Proposition\PropositionSearchService;

class PropositionRechercheControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): PropositionRechercheController
    {
        /**
         * @var PropositionSearchService $searchService
         */
        $searchService = $container->get(PropositionSearchService::class);

        $controller = new PropositionRechercheController();
        $controller->setSearchService($searchService);

        return $controller;
    }
}