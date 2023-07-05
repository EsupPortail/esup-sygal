<?php

namespace Doctorant\Service\Search;

use Doctorant\Service\DoctorantService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;

class DoctorantSearchServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DoctorantSearchService
    {
        $service = new DoctorantSearchService();
        
        /** @var \Individu\Service\IndividuService $individuService */
        $individuService = $container->get(IndividuService::class);
        $service->setIndividuService($individuService);

        /** @var DoctorantService $doctorantService */
        $doctorantService = $container->get(DoctorantService::class);
        $service->setDoctorantService($doctorantService);
        
        return $service;
    }
}