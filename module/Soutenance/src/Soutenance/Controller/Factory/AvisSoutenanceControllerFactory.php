<?php

namespace Soutenance\Controller\Factory;

use Application\Service\Acteur\ActeurService;
use Application\Service\Fichier\FichierService;
use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Application\Service\UserContextService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Validation\ValidationService;
use Soutenance\Controller\AvisSoutenanceController;
use Zend\Mvc\Controller\ControllerManager;

class AvisSoutenanceControllerFactory
{
    /**
     * @param ControllerManager $controllerManager
     * @return AvisSoutenanceController
     */
    public function __invoke(ControllerManager $controllerManager)
    {

        /**
         * @var TheseService $theseService
         * @var ValidationService $validationService
         * @var ActeurService $acteurService
         * @var NotifierService $notifierService
         * @var FichierService $fichierService
         * @var UserContextService $userContextService
         * @var UtilisateurService $utilisateurService
         *
         */
        $theseService = $controllerManager->getServiceLocator()->get('TheseService');
        $validationService = $controllerManager->getServiceLocator()->get('ValidationService');
        $acteurService = $controllerManager->getServiceLocator()->get(ActeurService::class);
        $notifierService = $controllerManager->getServiceLocator()->get(NotifierService::class);
        $fichierService = $controllerManager->getServiceLocator()->get('FichierService');
        $utilisateurService = $controllerManager->getServiceLocator()->get('UtilisateurService');
        $userContextService = $controllerManager->getServiceLocator()->get('UserContextService');

        /** @var AvisSoutenanceController $controller */
        $controller = new AvisSoutenanceController();
        $controller->setTheseService($theseService);
        $controller->setValidationService($validationService);
        $controller->setActeurService($acteurService);
        $controller->setNotifierService($notifierService);
        $controller->setFichierService($fichierService);
        $controller->setUtilisateurService($utilisateurService);
        $controller->setUserContextService($userContextService);

        return $controller;
    }
}