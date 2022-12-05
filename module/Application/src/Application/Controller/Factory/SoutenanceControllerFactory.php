<?php

namespace Application\Controller\Factory;

use Application\Controller\SoutenanceController;
use Application\Service\Actualite\ActualiteService;
use Information\Service\InformationService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;

class SoutenanceControllerFactory {

    /**
     * @param ContainerInterface $container
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : SoutenanceController
    {
        /**
         * @var ActualiteService $actualiteService
         * @var EcoleDoctoraleService $ecoleService
         * @var InformationService $informationService
         */
        $actualiteService = $container->get(ActualiteService::class);
        $ecoleService = $container->get(EcoleDoctoraleService::class);
        $informationService = $container->get(InformationService::class);

        $controller = new SoutenanceController();
        $controller->setActualiteService($actualiteService);
        $controller->setEcoleDoctoraleService($ecoleService);
        $controller->setInformationService($informationService);

        return $controller;
    }
}