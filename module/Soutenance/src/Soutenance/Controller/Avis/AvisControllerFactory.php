<?php

namespace Soutenance\Controller\Avis;

use Application\Service\Acteur\ActeurService;
use Application\Service\Fichier\FichierService;
use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Application\Service\UserContextService;
use Application\Service\Utilisateur\UtilisateurService;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Validation\ValidationService;
use Zend\Mvc\Controller\ControllerManager;

class AvisControllerFactory
{
    /**
     * @param ControllerManager $controllerManager
     * @return AvisController
     */
    public function __invoke(ControllerManager $controllerManager)
    {

        /**
         * @var MembreService $membreService
         * @var TheseService $theseService
         * @var ValidationService $validationService
         * @var ActeurService $acteurService
         * @var NotifierService $notifierService
         * @var FichierService $fichierService
         * @var UserContextService $userContextService
         * @var AvisService $avisService
         * @var UtilisateurService $utilisateurService
         *
         */
        $membreService = $controllerManager->getServiceLocator()->get(MembreService::class);
        $theseService = $controllerManager->getServiceLocator()->get('TheseService');
        $validationService = $controllerManager->getServiceLocator()->get(ValidationService::class);
        $acteurService = $controllerManager->getServiceLocator()->get(ActeurService::class);
        $notifierService = $controllerManager->getServiceLocator()->get(NotifierService::class);
        $fichierService = $controllerManager->getServiceLocator()->get('FichierService');
        $utilisateurService = $controllerManager->getServiceLocator()->get('UtilisateurService');
        $userContextService = $controllerManager->getServiceLocator()->get('UserContextService');
        $avisService = $controllerManager->getServiceLocator()->get(AvisService::class);

        /** @var AvisController $controller */
        $controller = new AvisController();
        $controller->setTheseService($theseService);
        $controller->setValidationService($validationService);
        $controller->setActeurService($acteurService);
        $controller->setNotifierService($notifierService);
        $controller->setFichierService($fichierService);
        $controller->setUtilisateurService($utilisateurService);
        $controller->setUserContextService($userContextService);
        $controller->setAvisService($avisService);
        $controller->setMembreService($membreService);

        return $controller;
    }
}