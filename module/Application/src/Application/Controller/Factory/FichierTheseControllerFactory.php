<?php

namespace Application\Controller\Factory;

use Application\Controller\FichierTheseController;
use Application\EventRouterReplacer;
use Application\Service\Fichier\FichierService;
use Application\Service\FichierThese\FichierTheseService;
use Application\Service\Individu\IndividuService;
use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Application\Service\Validation\ValidationService;
use Application\Service\VersionFichier\VersionFichierService;
use Zend\View\Renderer\PhpRenderer;
use Interop\Container\ContainerInterface;
use Zend\Router\Http\TreeRouteStack;

class FichierTheseControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return FichierTheseController
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var TreeRouteStack $httpRouter */
        $httpRouter = $container->get('HttpRouter');
        $cliConfig = $this->getCliConfig($container);

        /**
         * @var TheseService          $theseService
         * @var FichierService        $fichierService
         * @var FichierTheseService   $fichierTheseService
         * @var VersionFichierService $versionFichierService
         * @var NotifierService       $notificationService
         * @var IndividuService       $individuService
         * @var ValidationService     $validationService
         */
        $theseService = $container->get('TheseService');
        $fichierService = $container->get(FichierService::class);
        $fichierTheseService = $container->get('FichierTheseService');
        $versionFichierService = $container->get('VersionFichierService');
        $notificationService = $container->get(NotifierService::class);
        $individuService = $container->get('IndividuService');
        $validationService = $container->get('ValidationService');
        $eventRouterReplacer = new EventRouterReplacer($httpRouter, $cliConfig);

        /* @var $renderer PhpRenderer */
        $renderer = $container->get('ViewRenderer');

        $controller = new FichierTheseController();
        $controller->setTheseService($theseService);
        $controller->setFichierTheseService($fichierTheseService);
        $controller->setFichierService($fichierService);
        $controller->setVersionFichierService($versionFichierService);
        $controller->setNotifierService($notificationService);
        $controller->setIndividuService($individuService);
        $controller->setValidationService($validationService);
        $controller->setEventRouterReplacer($eventRouterReplacer);
        $controller->setRenderer($renderer);

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



