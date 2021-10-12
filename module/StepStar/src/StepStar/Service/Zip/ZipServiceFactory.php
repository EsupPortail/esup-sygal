<?php

namespace StepStar\Service\Zip;

use Application\Service\Fichier\FichierService;
use Application\Service\These\TheseService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ZipServiceFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /**
         * @var TheseService $theseService
         * @var FichierService $fichierService
         */
        $theseService = $container->get(TheseService::class);
        $fichierService = $container->get(FichierService::class);

        $service = new ZipService();
        $service->setTheseService($theseService);
        $service->setFichierService($fichierService);

        return $service;
    }
}