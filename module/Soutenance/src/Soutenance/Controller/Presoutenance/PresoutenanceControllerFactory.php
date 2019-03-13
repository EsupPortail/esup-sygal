<?php

namespace Soutenance\Controller\Presoutenance;

use Application\Service\Acteur\ActeurService;
use Application\Service\Fichier\FichierService;
use Application\Service\Individu\IndividuService;
use Application\Service\Role\RoleService;
use Application\Service\These\TheseService;
use Application\Service\Utilisateur\UtilisateurService;
use Soutenance\Form\DateRenduRapport\DateRenduRapportForm;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notifier\NotifierSoutenanceService;
use Soutenance\Service\Parametre\ParametreService;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Validation\ValidationService;
use Zend\Mvc\Controller\ControllerManager;

class PresoutenanceControllerFactory
{
    /**
     * @param ControllerManager $manager
     * @return  PresoutenanceController
     */
    public function __invoke(ControllerManager $manager)
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
         * @var EngagementImpartialiteService $engagementImpartialiteService
         */
        $propositionService = $manager->getServiceLocator()->get(PropositionService::class);
        $membreService = $manager->getServiceLocator()->get(MembreService::class);
        $theseService = $manager->getServiceLocator()->get('TheseService');
        $individuService = $manager->getServiceLocator()->get('IndividuService');
        $acteurService = $manager->getServiceLocator()->get(ActeurService::class);
        $notifierService = $manager->getServiceLocator()->get(NotifierSoutenanceService::class);
        $validationService = $manager->getServiceLocator()->get(ValidationService::class);
        $roleService = $manager->getServiceLocator()->get('RoleService');
        $avisService = $manager->getServiceLocator()->get(AvisService::class);
        $utilisateurService = $manager->getServiceLocator()->get('UtilisateurService');
        $fichierService = $manager->getServiceLocator()->get('FichierService');
        $parametreService = $manager->getServiceLocator()->get(ParametreService::class);
        $engagementImpartialiteService = $manager->getServiceLocator()->get(EngagementImpartialiteService::class);

        /**
         * @var DateRenduRapportForm $dateRenduRapportForm
         */
        $dateRenduRapportForm = $manager->getServiceLocator()->get('FormElementManager')->get(DateRenduRapportForm::class);

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
        $controller->setEngagementImpartialiteService($engagementImpartialiteService);

        $controller->setDateRenduRapportForm($dateRenduRapportForm);
        return $controller;
    }
}