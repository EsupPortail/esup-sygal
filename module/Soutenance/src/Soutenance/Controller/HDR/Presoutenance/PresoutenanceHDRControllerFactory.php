<?php

namespace Soutenance\Controller\HDR\Presoutenance;

use Acteur\Form\ActeurHDR\ActeurHDRForm;
use Acteur\Service\ActeurHDR\ActeurHDRService;
use Application\Service\Role\RoleService;
use Application\Service\Source\SourceService;
use Application\Service\Utilisateur\UtilisateurService;
use Fichier\Service\Fichier\FichierStorageService;
use HDR\Service\HDRService;
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
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRService;
use Soutenance\Service\Validation\ValidationHDR\ValidationHDRService;
use Structure\Service\StructureDocument\StructureDocumentService;
use UnicaenAuthentification\Service\User as UserService;
use UnicaenParametre\Service\Parametre\ParametreService;

class PresoutenanceHDRControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return PresoutenanceHDRController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): PresoutenanceHDRController
    {
        /**
         * @var HDRService $hdrService
         * @var ActeurHDRService $acteurService
         * @var AvisService $avisService
         * @var HorodatageService $horodatageService
         * @var MembreService $membreService
         * @var IndividuService $individuService
         * @var NotifierService $notifierService
         * @var ValidationHDRService $validationService
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
        $hdrService = $container->get(HDRService::class);
        $acteurService = $container->get(ActeurHDRService::class);
        $avisService = $container->get(AvisService::class);
        $horodatageService = $container->get(HorodatageService::class);
        $membreService = $container->get(MembreService::class);
        $individuService = $container->get(IndividuService::class);
        $notifierService = $container->get(NotifierService::class);
        $validationService = $container->get(ValidationHDRService::class);
        $roleService = $container->get('RoleService');
        $utilisateurService = $container->get('UtilisateurService');
        $userService = $container->get('unicaen-auth_user_service');
        $engagementImpartialiteService = $container->get(EngagementImpartialiteService::class);
        $structureDocumentService = $container->get(StructureDocumentService::class);
        $sourceService = $container->get(SourceService::class);
        $justificatifService = $container->get(JustificatifService::class);
        $fichierStorageService = $container->get(FichierStorageService::class);
        $parametreService = $container->get(ParametreService::class);
        $propositionHDRService = $container->get(PropositionHDRService::class);
        $acteurForm = $container->get('FormElementManager')->get(ActeurHDRForm::class);

        /**
         * @var DateRenduRapportForm $dateRenduRapportForm
         */
        $dateRenduRapportForm = $container->get('FormElementManager')->get(DateRenduRapportForm::class);

        /* @var $renderer PhpRenderer */
        $renderer = $container->get('ViewRenderer');

        $controller = new PresoutenanceHDRController();
        $controller->setHDRService($hdrService);
        $controller->setActeurHDRService($acteurService);
        $controller->setAvisService($avisService);
        $controller->setHorodatageService($horodatageService);
        $controller->setMembreService($membreService);
        $controller->setIndividuService($individuService);
        $controller->setNotifierService($notifierService);
        $controller->setValidationHDRService($validationService);
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
        $controller->setPropositionHDRService($propositionHDRService);

        $controller->setActeurHDRForm($acteurForm);

        $controller->setRenderer($renderer);

        /** @var SoutenanceNotificationFactory $soutenanceNotificationFactory */
        $soutenanceNotificationFactory = $container->get(SoutenanceNotificationFactory::class);
        $controller->setSoutenanceNotificationFactory($soutenanceNotificationFactory);

        return $controller;
    }
}