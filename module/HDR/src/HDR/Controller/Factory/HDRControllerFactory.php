<?php

namespace HDR\Controller\Factory;

use Depot\Service\Validation\DepotValidationService;
use HDR\Controller\HDRController;
use HDR\Service\HDRSearchService;
use HDR\Service\HDRService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Notification\Service\NotifierService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Service\Notification\SoutenanceNotificationFactory;

class HDRControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return HDRController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): HDRController
    {
        /**
         * @var HDRService       $hdrService
         * @var IndividuService         $indivdiService
         */
        $hdrService = $container->get(HDRService::class);

        $controller = new HDRController();
        $controller->setHDRService($hdrService);

        /** @var DepotValidationService $depotValidationService */
        $depotValidationService = $container->get(DepotValidationService::class);
        $controller->setDepotValidationService($depotValidationService);

        /** @var HDRSearchService $hdrSearchService */
        $hdrSearchService = $container->get(HDRSearchService::class);
        $controller->setHDRSearchService($hdrSearchService);

        /** @var SoutenanceNotificationFactory $soutenanceNotificationFactory */
        $soutenanceNotificationFactory = $container->get(SoutenanceNotificationFactory::class);
        $controller->setSoutenanceNotificationFactory($soutenanceNotificationFactory);

        /** @var NotifierService $notifierService */
        $notifierService = $container->get(NotifierService::class);
        $controller->setNotifierService($notifierService);

        return $controller;
    }
}