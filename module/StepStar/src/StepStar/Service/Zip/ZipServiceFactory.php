<?php

namespace StepStar\Service\Zip;

use Application\Service\FichierThese\FichierTheseService;
use Application\Service\These\TheseService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ZipServiceFactory implements FactoryInterface
{
    /**
     * @inheritDoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /**
         * @var TheseService $theseService
         * @var FichierTheseService $fichierTheseService
         */
        $theseService = $container->get(TheseService::class);
        $fichierTheseService = $container->get(FichierTheseService::class);

        $service = new ZipService();
        $service->setTheseService($theseService);
        $service->setFichierTheseService($fichierTheseService);

        return $service;
    }
}