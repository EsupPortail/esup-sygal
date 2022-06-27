<?php

namespace Application\Controller\Factory;

use Application\Controller\FichierTheseController;
use Application\EventRouterReplacer;
use Fichier\Service\Fichier\FichierService;
use Application\Service\FichierThese\FichierTheseService;
use Individu\Service\IndividuService;
use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Application\Service\Validation\ValidationService;
use Fichier\Service\Fichier\FichierStorageService;
use Fichier\Service\VersionFichier\VersionFichierService;
use Interop\Container\ContainerInterface;
use Laminas\EventManager\EventManager;
use Laminas\Router\Http\TreeRouteStack;

class FichierTheseControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return FichierTheseController
     */
    public function __invoke(ContainerInterface $container): FichierTheseController
    {
        /** @var TreeRouteStack $httpRouter */
        $httpRouter = $container->get('HttpRouter');
        $cliConfig = $this->getCliConfig($container);

        /**
         * @var TheseService          $theseService
         * @var FichierStorageService           $fileService
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

        $theseService->attach($eventManager);

        return $controller;
    }

    /**
     * @param ContainerInterface $container
     * @return array
     */
    private function getCliConfig(ContainerInterface $container)
    {
        $config = $container->get('Config');

        return [
            'domain' => isset($config['cli_config']['domain']) ? $config['cli_config']['domain'] : null,
            'scheme' => isset($config['cli_config']['scheme']) ? $config['cli_config']['scheme'] : null,
        ];
    }
}



