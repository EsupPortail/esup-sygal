<?php

namespace Application\Controller\Factory;

use Application\Controller\ActualiteController;
use Application\Service\Actualite\ActualiteService;
use Information\Service\InformationService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;

class ActualiteControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return ActualiteController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : ActualiteController
    {
        /**
         * @var ActualiteService $actualiteService
         * @var EcoleDoctoraleService $ecoleService
         * @var InformationService $informationService
         */
        $actualiteService = $container->get(ActualiteService::class);
        $ecoleService = $container->get(EcoleDoctoraleService::class);
        $informationService = $container->get(InformationService::class);

        $controller = new ActualiteController();
        $controller->setActualiteService($actualiteService);
        $controller->setEcoleDoctoraleService($ecoleService);
        $controller->setInformationService($informationService);

        return $controller;
    }
}