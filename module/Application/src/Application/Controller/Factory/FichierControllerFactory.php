<?php

namespace Application\Controller\Factory;

use Application\Controller\FichierController;
use Application\Service\Fichier\FichierService;
use Interop\Container\ContainerInterface;

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