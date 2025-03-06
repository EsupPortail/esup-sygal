<?php

namespace Depot\Controller\Factory;

use Application\EventRouterReplacer;
use Depot\Controller\FichierHDRController;
use Depot\Service\FichierHDR\FichierHDRService;
use Depot\Service\Notification\DepotNotificationFactory;
use Depot\Service\These\DepotService;
use Depot\Service\Validation\DepotValidationService;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Fichier\Service\VersionFichier\VersionFichierService;
use HDR\Service\HDRService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Laminas\EventManager\EventManager;
use Laminas\Router\Http\TreeRouteStack;
use Notification\Service\NotifierService;

class FichierHDRControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FichierHDRController
    {
        /** @var TreeRouteStack $httpRouter */
        $httpRouter = $container->get('HttpRouter');
        $cliConfig = $this->getCliConfig($container);

        /**
         * @var HDRService          $hdrService
         * @var FichierStorageService $fileService
         * @var FichierService        $fichierService
         * @var FichierHDRService   $fichierHDRService
         * @var VersionFichierService $versionFichierService
         * @var NotifierService       $notificationService
         * @var IndividuService       $individuService
         * @var EventManager          $eventManager
         */
        $hdrService = $container->get(HDRService::class);
        $fileService = $container->get(FichierStorageService::class);
        $fichierService = $container->get(FichierService::class);
        $fichierHDRService = $container->get(FichierHDRService::class);
        $versionFichierService = $container->get('VersionFichierService');
        $notificationService = $container->get(NotifierService::class);
        $individuService = $container->get(IndividuService::class);
        $eventRouterReplacer = new EventRouterReplacer($httpRouter, $cliConfig);
        $eventManager = $container->get('EventManager');

        $controller = new FichierHDRController();
        $controller->setHDRService($hdrService);
        $controller->setFichierHDRService($fichierHDRService);
        $controller->setFichierService($fichierService);
        $controller->setFichierStorageService($fileService);
        $controller->setVersionFichierService($versionFichierService);
        $controller->setNotifierService($notificationService);
        $controller->setIndividuService($individuService);
        $controller->setEventRouterReplacer($eventRouterReplacer);

        /** @var DepotService $depotService */
        $depotService = $container->get(DepotService::class);
        $controller->setDepotService($depotService);

        /** @var \Depot\Service\Validation\DepotValidationService $depotValidationService */
        $depotValidationService = $container->get(DepotValidationService::class);
        $controller->setDepotValidationService($depotValidationService);

        /** @var \Depot\Service\Notification\DepotNotificationFactory $depotNotificationFactory */
        $depotNotificationFactory = $container->get(DepotNotificationFactory::class);
        $controller->setDepotNotificationFactory($depotNotificationFactory);

        // gestion d'événements : DepotService écoute certains événement de FichierHDRController
        $controller->setEventManager($eventManager);
        $depotService->attach($eventManager);

        return $controller;
    }

    private function getCliConfig(ContainerInterface $container): array
    {
        $config = $container->get('Config');

        return [
            'domain' => $config['cli_config']['domain'] ?? null,
            'scheme' => $config['cli_config']['scheme'] ?? null,
        ];
    }
}



