<?php

namespace Individu\Service\Search;

use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;

class IndividuSearchServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): IndividuSearchService
    {
        /**
         * @var \Individu\Service\IndividuService $individuService
         */
        $individuService = $container->get(IndividuService::class);

        $service = new IndividuSearchService();
        $service->setIndividuService($individuService);

        return $service;
    }
}