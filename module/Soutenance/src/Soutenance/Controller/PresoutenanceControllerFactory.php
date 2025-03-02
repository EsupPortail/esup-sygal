<?php

namespace Soutenance\Controller;

use Application\Service\Role\RoleService;
use Application\Service\Source\SourceService;
use Application\Service\Utilisateur\UtilisateurService;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Laminas\View\Renderer\PhpRenderer;
use Notification\Service\NotifierService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Form\AdresseSoutenance\AdresseSoutenanceForm;
use Soutenance\Form\DateRenduRapport\DateRenduRapportForm;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\Horodatage\HorodatageService;
use Soutenance\Service\Justificatif\JustificatifService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notification\SoutenanceNotificationFactory;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Validation\ValidationService;
use Structure\Service\StructureDocument\StructureDocumentService;
use These\Form\Acteur\ActeurForm;
use These\Service\Acteur\ActeurService;
use These\Service\These\TheseService;
use UnicaenAuthentification\Service\User as UserService;
use UnicaenAuthToken\Service\TokenService;
use UnicaenParametre\Service\Parametre\ParametreService;

class PresoutenanceControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return PresoutenanceController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): PresoutenanceController
    {
        /**
         * @var PropositionService $propositionService
         * @var ActeurService $acteurService
         * @var AvisService $avisService
         * @var HorodatageService $horodatageService
         * @var MembreService $membreService
         * @var TheseService $theseService
         * @var IndividuService $individuService
         * @var NotifierService $notifierService
         * @var ValidationService $validationService
         * @var RoleService $roleService
         * @var UtilisateurService $utilisateurService
         * @var UserService $userService
         * @var EngagementImpartialiteService $engagementImpartialiteService
         * @var FichierService $fichierService
         * @var StructureDocumentService $structureDocumentService
         * @var TokenService $tokenService
         * @var SourceService $sourceService
         * @var JustificatifService $justificatifService
         * @var FichierStorageService $fichierStorageService
         * @var ParametreService $parametreService
         */
        $propositionService = $container->get(PropositionService::class);
        $acteurService = $container->get(ActeurService::class);
        $avisService = $container->get(AvisService::class);
        $horodatageService = $container->get(HorodatageService::class);
        $membreService = $container->get(MembreService::class);
        $theseService = $container->get('TheseService');
        $individuService = $container->get(IndividuService::class);
        $notifierService = $container->get(NotifierService::class);
        $validationService = $container->get(ValidationService::class);
        $roleService = $container->get('RoleService');
        $utilisateurService = $container->get('UtilisateurService');
        $userService = $container->get('unicaen-auth_user_service');
        $engagementImpartialiteService = $container->get(EngagementImpartialiteService::class);
        $fichierService = $container->get(FichierService::class);
        $structureDocumentService = $container->get(StructureDocumentService::class);
        $tokenService = $container->get(TokenService::class);
        $sourceService = $container->get(SourceService::class);
        $justificatifService = $container->get(JustificatifService::class);
        $fichierStorageService = $container->get(FichierStorageService::class);
        $parametreService = $container->get(ParametreService::class);
        $acteurForm = $container->get('FormElementManager')->get(ActeurForm::class);

        /**
         * @var DateRenduRapportForm $dateRenduRapportForm
         * @var AdresseSoutenanceForm $adresseSoutenanceForm
         */
        $dateRenduRapportForm = $container->get('FormElementManager')->get(DateRenduRapportForm::class);
        $adresseSoutenanceForm = $container->get('FormElementManager')->get(AdresseSoutenanceForm::class);

        /* @var $renderer PhpRenderer */
        $renderer = $container->get('ViewRenderer');

        $controller = new PresoutenanceController();
        $controller->setPropositionService($propositionService);
        $controller->setActeurService($acteurService);
        $controller->setAvisService($avisService);
        $controller->setHorodatageService($horodatageService);
        $controller->setMembreService($membreService);
        $controller->setTheseService($theseService);
        $controller->setIndividuService($individuService);
        $controller->setNotifierService($notifierService);
        $controller->setValidationService($validationService);
        $controller->setSourceService($sourceService);
        $controller->setApplicationRoleService($roleService);
        $controller->setUtilisateurService($utilisateurService);
        $controller->setUserService($userService);
        $controller->setEngagementImpartialiteService($engagementImpartialiteService);
        $controller->setFichierService($fichierService);
        $controller->setStructureDocumentService($structureDocumentService);
        $controller->setTokenService($tokenService);
        $controller->setJustificatifService($justificatifService);
        $controller->setFichierStorageService($fichierStorageService);
        $controller->setParametreService($parametreService);
        $controller->setDateRenduRapportForm($dateRenduRapportForm);
        $controller->setAdresseSoutenanceForm($adresseSoutenanceForm);
        $controller->setActeurForm($acteurForm);

        $controller->setRenderer($renderer);

        /** @var \Soutenance\Service\Notification\SoutenanceNotificationFactory $soutenanceNotificationFactory */
        $soutenanceNotificationFactory = $container->get(SoutenanceNotificationFactory::class);
        $controller->setSoutenanceNotificationFactory($soutenanceNotificationFactory);

        return $controller;
    }
}