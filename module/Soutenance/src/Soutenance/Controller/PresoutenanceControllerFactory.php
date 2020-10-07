<?php

namespace Soutenance\Controller;

use Application\Service\Acteur\ActeurService;
use Application\Service\Individu\IndividuService;
use Application\Service\Role\RoleService;
use Application\Service\These\TheseService;
use Application\Service\Utilisateur\UtilisateurService;
use Interop\Container\ContainerInterface;
use Soutenance\Form\AdresseSoutenance\AdresseSoutenanceForm;
use Soutenance\Form\DateRenduRapport\DateRenduRapportForm;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notifier\NotifierSoutenanceService;
use Soutenance\Service\Parametre\ParametreService;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Validation\ValidationService;
use UnicaenAuth\Service\User as UserService;
use Zend\View\Renderer\PhpRenderer;

class PresoutenanceControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return PresoutenanceController
     */
    public function __invoke(ContainerInterface $container)
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
         * @var UserService $userService
         * @var ParametreService $parametreService
         * @var EngagementImpartialiteService $engagementImpartialiteService
         */
        $propositionService = $container->get(PropositionService::class);
        $membreService = $container->get(MembreService::class);
        $theseService = $container->get('TheseService');
        $individuService = $container->get('IndividuService');
        $acteurService = $container->get(ActeurService::class);
        $notifierService = $container->get(NotifierSoutenanceService::class);
        $validationService = $container->get(ValidationService::class);
        $roleService = $container->get('RoleService');
        $avisService = $container->get(AvisService::class);
        $utilisateurService = $container->get('UtilisateurService');
        $userService = $container->get('unicaen-auth_user_service');
        $parametreService = $container->get(ParametreService::class);
        $engagementImpartialiteService = $container->get(EngagementImpartialiteService::class);

        /**
         * @var DateRenduRapportForm $dateRenduRapportForm
         * @var AdresseSoutenanceForm $adresseSoutenanceForm
         */
        $dateRenduRapportForm = $container->get('FormElementManager')->get(DateRenduRapportForm::class);
        $adresseSoutenanceForm = $container->get('FormElementManager')->get(AdresseSoutenanceForm::class);

        /* @var $renderer PhpRenderer */
        $renderer = $container->get('ViewRenderer');

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
        $controller->setUserService($userService);
        $controller->setParametreService($parametreService);
        $controller->setEngagementImpartialiteService($engagementImpartialiteService);

        $controller->setDateRenduRapportForm($dateRenduRapportForm);
        $controller->setAdresseSoutenanceForm($adresseSoutenanceForm);

        $controller->setRenderer($renderer);
        return $controller;
    }
}