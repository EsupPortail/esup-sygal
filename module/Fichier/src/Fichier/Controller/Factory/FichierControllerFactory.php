<?php

namespace Fichier\Controller\Factory;

use Fichier\Controller\FichierController;
use Fichier\Service\Fichier\FichierService;
use Psr\Container\ContainerInterface;

class FichierControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return FichierController
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var FichierService $fichierService */
        $fichierService = $container->get(FichierService::class);

        $service = new FichierController();
        $service->setFichierService($fichierService);

        return $service;
    }
}