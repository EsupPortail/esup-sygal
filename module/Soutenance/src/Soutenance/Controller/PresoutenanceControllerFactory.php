<?php

namespace Soutenance\Controller;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Service\Notification\NotificationService;
use These\Service\Acteur\ActeurService;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Individu\Service\IndividuService;
use Application\Service\Role\RoleService;
use Application\Service\Source\SourceService;
use Structure\Service\StructureDocument\StructureDocumentService;
use These\Service\These\TheseService;
use Application\Service\Utilisateur\UtilisateurService;
use Interop\Container\ContainerInterface;
use Soutenance\Form\AdresseSoutenance\AdresseSoutenanceForm;
use Soutenance\Form\DateRenduRapport\DateRenduRapportForm;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\Evenement\EvenementService;
use Soutenance\Service\Justificatif\JustificatifService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notifier\NotifierService;
use Soutenance\Service\Parametre\ParametreService;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Validation\ValidationService;
use UnicaenAuth\Service\User as UserService;
use UnicaenAuthToken\Service\TokenService;
use Laminas\View\Renderer\PhpRenderer;
use UnicaenRenderer\Service\Rendu\RenduService;

class PresoutenanceControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return PresoutenanceController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : PresoutenanceController
    {
        /**
         * @var PropositionService $propositionService
         * @var AvisService $avisService
         * @var EvenementService $evenementService
         * @var MembreService $membreService
         * @var TheseService $theseService
         * @var IndividuService $individuService
         * @var NotifierService $notifierService
         * @var ActeurService $acteurService
         * @var ValidationService $validationService
         * @var RoleService $roleService
         * @var UtilisateurService $utilisateurService
         * @var UserService $userService
         * @var ParametreService $parametreService
         * @var EngagementImpartialiteService $engagementImpartialiteService
         * @var FichierService $fichierService
         * @var StructureDocumentService $structureDocumentService
         * @var TokenService $tokenService
         * @var SourceService $sourceService
         * @var JustificatifService $justificatifService
         * @var FichierStorageService $fichierStorageService
         * @var NotificationService $notificationService
         */
        $evenementService = $container->get(EvenementService::class);
        $propositionService = $container->get(PropositionService::class);
        $membreService = $container->get(MembreService::class);
        $theseService = $container->get('TheseService');
        $individuService = $container->get(IndividuService::class);
        $acteurService = $container->get(ActeurService::class);
        $notifierService = $container->get(NotifierService::class);
        $validationService = $container->get(ValidationService::class);
        $roleService = $container->get('RoleService');
        $avisService = $container->get(AvisService::class);
        $utilisateurService = $container->get('UtilisateurService');
        $userService = $container->get('unicaen-auth_user_service');
        $parametreService = $container->get(ParametreService::class);
        $engagementImpartialiteService = $container->get(EngagementImpartialiteService::class);
        $fichierService = $container->get(FichierService::class);
        $structureDocumentService = $container->get(StructureDocumentService::class);
        $tokenService = $container->get(TokenService::class);
        $sourceService = $container->get(SourceService::class);
        $justificatifService = $container->get(JustificatifService::class);
        $fichierStorageService = $container->get(FichierStorageService::class);
        $notificationService = $container->get(NotificationService::class);

        /**
         * @var DateRenduRapportForm $dateRenduRapportForm
         * @var AdresseSoutenanceForm $adresseSoutenanceForm
         */
        $dateRenduRapportForm = $container->get('FormElementManager')->get(DateRenduRapportForm::class);
        $adresseSoutenanceForm = $container->get('FormElementManager')->get(AdresseSoutenanceForm::class);

        /* @var $renderer PhpRenderer */
        $renderer = $container->get('ViewRenderer');

        $controller = new PresoutenanceController();
        $controller->setEvenementService($evenementService);
        $controller->setPropositionService($propositionService);
        $controller->setMembreService($membreService);
        $controller->setTheseService($theseService);
        $controller->setIndividuService($individuService);
        $controller->setActeurService($acteurService);
        $controller->setSoutenanceNotifierService($notifierService);
        $controller->setValidationService($validationService);
        $controller->setSourceService($sourceService);
        $controller->setRoleService($roleService);
        $controller->setAvisService($avisService);
        $controller->setUtilisateurService($utilisateurService);
        $controller->setUserService($userService);
        $controller->setParametreService($parametreService);
        $controller->setEngagementImpartialiteService($engagementImpartialiteService);
        $controller->setFichierService($fichierService);
        $controller->setStructureDocumentService($structureDocumentService);
        $controller->setTokenService($tokenService);
        $controller->setJustificatifService($justificatifService);
        $controller->setFichierStorageService($fichierStorageService);
        $controller->setNotificationService($notificationService);

        $controller->setDateRenduRapportForm($dateRenduRapportForm);
        $controller->setAdresseSoutenanceForm($adresseSoutenanceForm);

        $controller->setRenderer($renderer);
        return $controller;
    }
}