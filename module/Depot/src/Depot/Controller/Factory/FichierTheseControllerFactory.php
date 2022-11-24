<?php

namespace Depot\Controller\Factory;

use Depot\Service\These\DepotService;
use Depot\Controller\FichierTheseController;
use Application\EventRouterReplacer;
use Depot\Service\Validation\DepotValidationService;
use Fichier\Service\Fichier\FichierService;
use Depot\Service\FichierThese\FichierTheseService;
use Individu\Service\IndividuService;
use Application\Service\Notification\NotifierService;
use These\Service\These\TheseService;
use Application\Service\Validation\ValidationService;
use Fichier\Service\Fichier\FichierStorageService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Interop\Container\ContainerInterface;
use Laminas\EventManager\EventManager;
use Laminas\Router\Http\TreeRouteStack;

class FichierTheseControllerFactory
{
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
         * @var ValidationService     $validationService
         * @var EventManager          $eventManager
         */
        $theseService = $container->get('TheseService');
        $fileService = $container->get(FichierStorageService::class);
        $fichierService = $container->get(FichierService::class);
        $fichierTheseService = $container->get('FichierTheseService');
        $versionFichierService = $container->get('VersionFichierService');
        $notificationService = $container->get(NotifierService::class);
        $individuService = $container->get(IndividuService::class);
        $validationService = $container->get('ValidationService');
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
        $controller->setValidationService($validationService);
        $controller->setEventRouterReplacer($eventRouterReplacer);
        $controller->setEventManager($eventManager);

        /** @var DepotService $depotService */
        $depotService = $container->get(DepotService::class);
        $depotService->attach($eventManager);
        $controller->setDepotService($depotService);

        /** @var \Depot\Service\Validation\DepotValidationService $depotValidationService */
        $depotValidationService = $container->get(DepotValidationService::class);
        $controller->setDepotValidationService($depotValidationService);

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



