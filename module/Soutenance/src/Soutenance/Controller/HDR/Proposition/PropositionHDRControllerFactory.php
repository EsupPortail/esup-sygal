<?php

namespace Soutenance\Controller\HDR\Proposition;

use Acteur\Service\ActeurHDR\ActeurHDRService;
use Application\Service\Role\RoleService;
use Application\Service\UserContextService;
use Fichier\Service\Fichier\FichierStorageService;
use HDR\Service\HDRService;
use Information\Service\InformationService;
use Interop\Container\ContainerInterface;
use Laminas\View\Renderer\PhpRenderer;
use Notification\Service\NotifierService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Assertion\HDR\PropositionHDRAssertion;
use Soutenance\Form\AdresseSoutenance\AdresseSoutenanceForm;
use Soutenance\Form\Anglais\AnglaisForm;
use Soutenance\Form\Confidentialite\ConfidentialiteForm;
use Soutenance\Form\DateLieu\DateLieuForm;
use Soutenance\Form\Membre\MembreForm;
use Soutenance\Form\Refus\RefusForm;
use Soutenance\Service\Adresse\AdresseService;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\Horodatage\HorodatageService;
use Soutenance\Service\Justificatif\JustificatifService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notification\SoutenanceNotificationFactory;
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRService;
use Soutenance\Service\Validation\ValidationHDR\ValidationHDRService;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use UnicaenParametre\Service\Parametre\ParametreService;
use UnicaenRenderer\Service\Rendu\RenduService;

class PropositionHDRControllerFactory
{

    /**
     * @param ContainerInterface $container
     * @return PropositionHDRController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): PropositionHDRController
    {
        /**
         * @var HDRService $hdrService
         * @var ActeurHDRService $acteurService
         * @var AdresseService $adresseService
         * @var AvisService $avisService
         * @var EcoleDoctoraleService $ecoleDoctoraleService
         * @var EtablissementService $etablissementService
         * @var InformationService $informationService
         * @var FichierStorageService $fichierStorageService
         * @var HorodatageService $horodatageService
         * @var MembreService $membreService
         * @var NotifierService $notifierService
         * @var RoleService $roleService
         * @var UserContextService $userContextService
         * @var ValidationHDRService $validationService
         * @var JustificatifService $justificatifService
         * @var ParametreService $parametreService
         * @var RenduService $renduService
         * @var PropositionHDRService $propositionHDRService
         */
        $hdrService = $container->get(HDRService::class);
        $acteurService = $container->get(ActeurHDRService::class);
        $adresseService = $container->get(AdresseService::class);
        $avisService = $container->get(AvisService::class);
        $ecoleDoctoraleService = $container->get(EcoleDoctoraleService::class);
        $etablissementService = $container->get(EtablissementService::class);
        $fichierStorageService = $container->get(FichierStorageService::class);
        $horodatageService = $container->get(HorodatageService::class);
        $informationService = $container->get(InformationService::class);
        $membreService = $container->get(MembreService::class);
        $notifierService = $container->get(NotifierService::class);
        $roleService = $container->get(RoleService::class);
        $userContextService = $container->get('UserContextService');
        $validationService = $container->get(ValidationHDRService::class);
        $justificatifService = $container->get(JustificatifService::class);
        $parametreService = $container->get(ParametreService::class);
        $renduService = $container->get(RenduService::class);
        $propositionHDRService = $container->get(PropositionHDRService::class);

        /**
         * @var AdresseSoutenanceForm $adresseForm
         * @var DateLieuForm $dateLieuForm
         * @var MembreForm $membreForm
         * @var AnglaisForm $anglaisForm
         * @var ConfidentialiteForm $confidentialiteForm
         * @var RefusForm $refusForm
         */
        $adresseForm = $container->get('FormElementManager')->get(AdresseSoutenanceForm::class);
        $dateLieuForm = $container->get('FormElementManager')->get(DateLieuForm::class);
        $membreForm = $container->get('FormElementManager')->get(MembreForm::class);
        $anglaisForm = $container->get('FormElementManager')->get(AnglaisForm::class);
        $confidentialiteForm = $container->get('FormElementManager')->get(ConfidentialiteForm::class);
        $refusForm = $container->get('FormElementManager')->get(RefusForm::class);

        /* @var $renderer PhpRenderer */
        $renderer = $container->get('ViewRenderer');

        /**
         * @var PropositionHDRAssertion $propositionHDRAssertion
         */
        $propositionHDRAssertion = $container->get(PropositionHDRAssertion::class);

        $controller = new PropositionHDRController();

        $controller->setHDRService($hdrService);
        $controller->setActeurHDRService($acteurService);
        $controller->setAdresseService($adresseService);
        $controller->setAvisService($avisService);
        $controller->setEcoleDoctoraleService($ecoleDoctoraleService);
        $controller->setEtablissementService($etablissementService);
        $controller->setFichierStorageService($fichierStorageService);
        $controller->setHorodatageService($horodatageService);
        $controller->setInformationService($informationService);
        $controller->setMembreService($membreService);
        $controller->setNotifierService($notifierService);
        $controller->setApplicationRoleService($roleService);
        $controller->setUserContextService($userContextService);
        $controller->setValidationHDRService($validationService);
        $controller->setJustificatifService($justificatifService);
        $controller->setParametreService($parametreService);
        $controller->setRenduService($renduService);
        $controller->setPropositionHDRService($propositionHDRService);

        $controller->setAdresseSoutenanceForm($adresseForm);
        $controller->setDateLieuForm($dateLieuForm);
        $controller->setMembreForm($membreForm);
        $controller->setAnglaisForm($anglaisForm);
        $controller->setConfidentialiteForm($confidentialiteForm);
        $controller->setRefusForm($refusForm);

        $controller->setRenderer($renderer);

        $controller->setPropositionHDRAssertion($propositionHDRAssertion);

        /** @var SoutenanceNotificationFactory $soutenanceNotificationFactory */
        $soutenanceNotificationFactory = $container->get(SoutenanceNotificationFactory::class);
        $controller->setSoutenanceNotificationFactory($soutenanceNotificationFactory);

        return $controller;
    }
}