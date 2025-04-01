<?php

namespace Fichier\Service\Fichier\Search;

use Fichier\Service\Fichier\FichierService;
use Interop\Container\ContainerInterface;

class FichierSearchServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FichierSearchService
    {
        $service = new FichierSearchService();
        
        /** @var \Fichier\Service\Fichier\FichierService $individuService */
        $individuService = $container->get(FichierService::class);
        $service->setFichierService($individuService);

        return $service;
    }
}