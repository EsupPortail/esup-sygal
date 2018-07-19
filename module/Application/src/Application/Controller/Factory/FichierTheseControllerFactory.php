<?php

namespace Application\Controller\Factory;

use Application\Controller\FichierTheseController;
use Application\Service\Fichier\FichierService;
use Application\Service\Individu\IndividuService;
use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Application\Service\Validation\ValidationService;
use Application\Service\VersionFichier\VersionFichierService;
use Zend\Mvc\Controller\ControllerManager;

class FichierTheseControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return FichierTheseController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $serviceLocator = $controllerManager->getServiceLocator();

        /**
         * @var TheseService          $theseService
         * @var FichierService        $fichierService
         * @var VersionFichierService $versionFichierService
         * @var NotifierService       $notificationService
         * @var IndividuService       $individuService
         * @var ValidationService     $validationService
         */
        $theseService = $serviceLocator->get('TheseService');
        $fichierService = $serviceLocator->get('FichierService');
        $versionFichierService = $serviceLocator->get('VersionFichierService');
        $notificationService = $serviceLocator->get(NotifierService::class);
        $individuService = $serviceLocator->get('IndividuService');
        $validationService = $serviceLocator->get('ValidationService');

        $controller = new FichierTheseController();
        $controller->setTheseService($theseService);
        $controller->setFichierService($fichierService);
        $controller->setVersionFichierService($versionFichierService);
        $controller->setNotifierService($notificationService);
        $controller->setIndividuService($individuService);
        $controller->setValidationService($validationService);

        return $controller;
    }
}



