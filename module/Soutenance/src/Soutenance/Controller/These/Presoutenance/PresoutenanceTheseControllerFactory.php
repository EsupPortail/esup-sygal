<?php

namespace Soutenance\Controller\These\Presoutenance;

use Acteur\Form\ActeurThese\ActeurTheseForm;
use Acteur\Service\ActeurThese\ActeurTheseService;
use Application\Service\Role\RoleService;
use Application\Service\Source\SourceService;
use Application\Service\Utilisateur\UtilisateurService;
use Fichier\Service\Fichier\FichierStorageService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Laminas\View\Renderer\PhpRenderer;
use Notification\Service\NotifierService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Form\DateRenduRapport\DateRenduRapportForm;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\Horodatage\HorodatageService;
use Soutenance\Service\Justificatif\JustificatifService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notification\SoutenanceNotificationFactory;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseService;
use Soutenance\Service\Validation\ValidationThese\ValidationTheseService;
use Structure\Service\StructureDocument\StructureDocumentService;
use These\Service\These\TheseService;
use UnicaenAuthentification\Service\User as UserService;
use UnicaenParametre\Service\Parametre\ParametreService;

class PresoutenanceTheseControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return PresoutenanceTheseController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): PresoutenanceTheseController
    {
        /**
         * @var PropositionService $propositionService
         * @var ActeurTheseService $acteurService
         * @var AvisService $avisService
         * @var HorodatageService $horodatageService
         * @var MembreService $membreService
         * @var TheseService $theseService
         * @var IndividuService $individuService
         * @var NotifierService $notifierService
         * @var ValidationTheseService $validationService
         * @var RoleService $roleService
         * @var UtilisateurService $utilisateurService
         * @var UserService $userService
         * @var EngagementImpartialiteService $engagementImpartialiteService
         * @var StructureDocumentService $structureDocumentService
         * @var SourceService $sourceService
         * @var JustificatifService $justificatifService
         * @var FichierStorageService $fichierStorageService
         * @var ParametreService $parametreService
         */
        $acteurService = $container->get(ActeurTheseService::class);
        $avisService = $container->get(AvisService::class);
        $horodatageService = $container->get(HorodatageService::class);
        $membreService = $container->get(MembreService::class);
        $theseService = $container->get('TheseService');
        $individuService = $container->get(IndividuService::class);
        $notifierService = $container->get(NotifierService::class);
        $validationService = $container->get(ValidationTheseService::class);
        $roleService = $container->get('RoleService');
        $utilisateurService = $container->get('UtilisateurService');
        $userService = $container->get('unicaen-auth_user_service');
        $engagementImpartialiteService = $container->get(EngagementImpartialiteService::class);
        $structureDocumentService = $container->get(StructureDocumentService::class);
        $sourceService = $container->get(SourceService::class);
        $justificatifService = $container->get(JustificatifService::class);
        $fichierStorageService = $container->get(FichierStorageService::class);
        $parametreService = $container->get(ParametreService::class);
        $propositionTheseService = $container->get(PropositionTheseService::class);
        $acteurForm = $container->get('FormElementManager')->get(ActeurTheseForm::class);

        /**
         * @var DateRenduRapportForm $dateRenduRapportForm
         */
        $dateRenduRapportForm = $container->get('FormElementManager')->get(DateRenduRapportForm::class);

        /* @var $renderer PhpRenderer */
        $renderer = $container->get('ViewRenderer');

        $controller = new PresoutenanceTheseController();
        $controller->setActeurTheseService($acteurService);
        $controller->setAvisService($avisService);
        $controller->setHorodatageService($horodatageService);
        $controller->setMembreService($membreService);
        $controller->setTheseService($theseService);
        $controller->setIndividuService($individuService);
        $controller->setNotifierService($notifierService);
        $controller->setValidationTheseService($validationService);
        $controller->setSourceService($sourceService);
        $controller->setApplicationRoleService($roleService);
        $controller->setUtilisateurService($utilisateurService);
        $controller->setUserService($userService);
        $controller->setEngagementImpartialiteService($engagementImpartialiteService);
        $controller->setStructureDocumentService($structureDocumentService);
        $controller->setJustificatifService($justificatifService);
        $controller->setFichierStorageService($fichierStorageService);
        $controller->setParametreService($parametreService);
        $controller->setDateRenduRapportForm($dateRenduRapportForm);
        $controller->setPropositionTheseService($propositionTheseService);
        $controller->setActeurTheseForm($acteurForm);

        $controller->setRenderer($renderer);

        /** @var \Soutenance\Service\Notification\SoutenanceNotificationFactory $soutenanceNotificationFactory */
        $soutenanceNotificationFactory = $container->get(SoutenanceNotificationFactory::class);
        $controller->setSoutenanceNotificationFactory($soutenanceNotificationFactory);

        return $controller;
    }
}