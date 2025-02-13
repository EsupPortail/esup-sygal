<?php

namespace Soutenance\Controller\These\PropositionThese;

use Interop\Container\ContainerInterface;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseSearchService;

class PropositionTheseRechercheControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): PropositionTheseRechercheController
    {
        /**
         * @var PropositionTheseSearchService $searchService
         */
        $searchService = $container->get(PropositionTheseSearchService::class);

        $controller = new PropositionTheseRechercheController();
        $controller->setSearchService($searchService);

        return $controller;
    }
}