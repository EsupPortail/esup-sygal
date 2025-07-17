<?php

namespace Doctorant\Controller;

use Doctorant\Service\Search\DoctorantSearchService;
use Interop\Container\ContainerInterface;

class DoctorantRechercheControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DoctorantRechercheController
    {
        $controller = new DoctorantRechercheController();

        /** @var \Doctorant\Service\DoctorantService $doctorantService */
        $doctorantService = $container->get('DoctorantService');
        $controller->setDoctorantService($doctorantService);

        /** @var DoctorantSearchService $doctorantSearchService */
        $doctorantSearchService = $container->get(DoctorantSearchService::class);
        $controller->setSearchService($doctorantSearchService);

        return $controller;
    }
}