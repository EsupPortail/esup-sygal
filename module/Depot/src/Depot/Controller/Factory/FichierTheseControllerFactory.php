<?php

namespace Depot\Controller\Factory;

use Application\EventRouterReplacer;
use Validation\Service\ValidationThese\ValidationTheseService;
use Depot\Controller\FichierTheseController;
use Depot\Service\FichierThese\FichierTheseService;
use Depot\Service\Notification\DepotNotificationFactory;
use Notification\Service\NotifierService;
use Depot\Service\These\DepotService;
use Depot\Service\Validation\DepotValidationService;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Laminas\EventManager\EventManager;
use Laminas\Router\Http\TreeRouteStack;
use These\Service\These\TheseService;

class FichierTheseControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FichierTheseController
    {
        /** @var TreeRouteStack $httpRouter */
        $httpRouter = $container->get('HttpRouter');
        $cliConfig = $this->getCliConfig($container);

        /**
         * @var TheseService          $theseService
         * @var FichierStorageService $fileService
         * @var FichierService        $fichierService
         * @var FichierTheseService   $fichierTheseService
         * @var VersionFichierService $versionFichierService
         * @var NotifierService       $notificationService
         * @var IndividuService       $individuService
         * @var ValidationTheseService     $validationService
         * @var EventManager          $eventManager
         */
        $theseService = $container->get('TheseService');
        $fileService = $container->get(FichierStorageService::class);
        $fichierService = $container->get(FichierService::class);
        $fichierTheseService = $container->get('FichierTheseService');
        $versionFichierService = $container->get('VersionFichierService');
        $notificationService = $container->get(NotifierService::class);
        $individuService = $container->get(IndividuService::class);
        $validationService = $container->get(ValidationTheseService::class);
        $eventRouterReplacer = new EventRouterReplacer($httpRouter, $cliConfig);
        $eventManager = $container->get('EventManager');

        $controller = new FichierTheseController();
        $controller->setTheseService($theseService);
        $controller->setFichierTheseService($fichierTheseService);
        $controller->setFichierService($fichierService);
        $controller->setFichierStorageService($fileService);
        $controller->setVersionFichierService($versionFichierService);
        $controller->setNotifierService($notificationService);
        $controller->setIndividuService($individuService);
        $controller->setValidationTheseService($validationService);
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

        // gestion d'événements : DepotService écoute certains événement de FichierTheseController
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



