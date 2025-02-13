<?php

namespace Soutenance\Controller\HDR\Proposition;

use Interop\Container\ContainerInterface;
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRSearchService;

class PropositionHDRRechercheControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): PropositionHDRRechercheController
    {
        /**
         * @var PropositionHDRSearchService $searchService
         */
        $searchService = $container->get(PropositionHDRSearchService::class);

        $controller = new PropositionHDRRechercheController();
        $controller->setSearchService($searchService);

        return $controller;
    }
}