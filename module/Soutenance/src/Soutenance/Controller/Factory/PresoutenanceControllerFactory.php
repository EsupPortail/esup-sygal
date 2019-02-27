<?php

namespace Soutenance\Controller\Factory;

use Application\Service\Acteur\ActeurService;
use Application\Service\Fichier\FichierService;
use Application\Service\Individu\IndividuService;
use Application\Service\Role\RoleService;
use Application\Service\These\TheseService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Validation\ValidationService;
use Soutenance\Controller\PresoutenanceController;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notifier\NotifierSoutenanceService;
use Soutenance\Service\Parametre\ParametreService;
use Soutenance\Service\Proposition\PropositionService;
use Zend\Mvc\Controller\ControllerManager;

class PresoutenanceControllerFactory
{
    /**
     * @param ControllerManager $controllerManager
     * @return  PresoutenanceController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        /**
         * @var PropositionService $propositionService
         * @var AvisService $avisService
         * @var MembreService $membreService
         * @var TheseService $theseService
         * @var IndividuService $individuService
         * @var NotifierSoutenanceService $notifierService
         * @var ActeurService $acteurService
         * @var ValidationService $validationService
         * @var RoleService $roleService
         * @var UtilisateurService $utilisateurService
         * @var FichierService $fichierService
         * @var ParametreService $parametreService
         */
        $propositionService = $controllerManager->getServiceLocator()->get(PropositionService::class);
        $membreService = $controllerManager->getServiceLocator()->get(MembreService::class);
        $theseService = $controllerManager->getServiceLocator()->get('TheseService');
        $individuService = $controllerManager->getServiceLocator()->get('IndividuService');
        $acteurService = $controllerManager->getServiceLocator()->get(ActeurService::class);
        $notifierService = $controllerManager->getServiceLocator()->get(NotifierSoutenanceService::class);
        $validationService = $controllerManager->getServiceLocator()->get('ValidationService');
        $roleService = $controllerManager->getServiceLocator()->get('RoleService');
        $avisService = $controllerManager->getServiceLocator()->get(AvisService::class);
        $utilisateurService = $controllerManager->getServiceLocator()->get('UtilisateurService');
        $fichierService = $controllerManager->getServiceLocator()->get('FichierService');
        $parametreService = $controllerManager->getServiceLocator()->get(ParametreService::class);

        /** @var PresoutenanceController $controller */
        $controller = new PresoutenanceController();
        $controller->setPropositionService($propositionService);
        $controller->setMembreService($membreService);
        $controller->setTheseService($theseService);
        $controller->setIndividuService($individuService);
        $controller->setActeurService($acteurService);
        $controller->setNotifierSoutenanceService($notifierService);
        $controller->setValidationService($validationService);
        $controller->setRoleService($roleService);
        $controller->setAvisService($avisService);
        $controller->setUtilisateurService($utilisateurService);
        $controller->setFichierService($fichierService);
        $controller->setParametreService($parametreService);

        return $controller;
    }
}