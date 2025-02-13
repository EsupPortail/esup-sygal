<?php

namespace Soutenance\Controller\These\PropositionThese;

use Acteur\Service\ActeurThese\ActeurTheseService;
use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManager;
use Application\Service\Role\RoleService;
use Application\Service\UserContextService;
use Fichier\Service\Fichier\FichierStorageService;
use Information\Service\InformationService;
use Interop\Container\ContainerInterface;
use Laminas\View\Renderer\PhpRenderer;
use Notification\Service\NotifierService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Assertion\These\PropositionTheseAssertion;
use Soutenance\Controller\PropositionController;
use Soutenance\Form\AdresseSoutenance\AdresseSoutenanceForm;
use Soutenance\Form\Anglais\AnglaisForm;
use Soutenance\Form\ChangementTitre\ChangementTitreForm;
use Soutenance\Form\Confidentialite\ConfidentialiteForm;
use Soutenance\Form\DateLieu\DateLieuForm;
use Soutenance\Form\LabelEuropeen\LabelEuropeenForm;
use Soutenance\Form\Membre\MembreForm;
use Soutenance\Form\Refus\RefusForm;
use Soutenance\Service\Adresse\AdresseService;
use Soutenance\Service\Avis\AvisService;
use Soutenance\Service\Horodatage\HorodatageService;
use Soutenance\Service\Justificatif\JustificatifService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notification\SoutenanceNotificationFactory;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseService;
use Soutenance\Service\Validation\ValidationThese\ValidationTheseService;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use These\Service\These\TheseService;
use UnicaenParametre\Service\Parametre\ParametreService;
use UnicaenRenderer\Service\Rendu\RenduService;

class PropositionTheseControllerFactory
{

    /**
     * @param ContainerInterface $container
     * @return PropositionController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): PropositionTheseController
    {
        /**
         * @var TheseService $theseService
         * @var ActeurTheseService $acteurService
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
         * @var ValidationTheseService $validationService
         * @var JustificatifService $justificatifService
         * @var ParametreService $parametreService
         * @var RenduService $renduService
         * @var PropositionTheseService $propositionTheseService
         */
        $theseService = $container->get(TheseService::class);
        $acteurService = $container->get(ActeurTheseService::class);
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
        $validationService = $container->get(ValidationTheseService::class);
        $justificatifService = $container->get(JustificatifService::class);
        $parametreService = $container->get(ParametreService::class);
        $renduService = $container->get(RenduService::class);
        $propositionTheseService = $container->get(PropositionTheseService::class);

        /**
         * @var AdresseSoutenanceForm $adresseForm
         * @var DateLieuForm $dateLieuForm
         * @var MembreForm $membreForm
         * @var LabelEuropeenForm $labelEuropeenForm
         * @var AnglaisForm $anglaisForm
         * @var ConfidentialiteForm $confidentialiteForm
         * @var RefusForm $refusForm
         * @var ChangementTitreForm $changementTitreForm
         */
        $adresseForm = $container->get('FormElementManager')->get(AdresseSoutenanceForm::class);
        $dateLieuForm = $container->get('FormElementManager')->get(DateLieuForm::class);
        $membreForm = $container->get('FormElementManager')->get(MembreForm::class);
        $labelEuropeenForm = $container->get('FormElementManager')->get(LabelEuropeenForm::class);
        $anglaisForm = $container->get('FormElementManager')->get(AnglaisForm::class);
        $confidentialiteForm = $container->get('FormElementManager')->get(ConfidentialiteForm::class);
        $refusForm = $container->get('FormElementManager')->get(RefusForm::class);
        $changementTitreForm = $container->get('FormElementManager')->get(ChangementTitreForm::class);

        /* @var $renderer PhpRenderer */
        $renderer = $container->get('ViewRenderer');

        /**
         * @var PropositionTheseAssertion $propositionTheseAssertion
         */
        $propositionTheseAssertion = $container->get(PropositionTheseAssertion::class);

        $controller = new PropositionTheseController();

        $controller->setTheseService($theseService);
        $controller->setActeurTheseService($acteurService);
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
        $controller->setValidationTheseService($validationService);
        $controller->setJustificatifService($justificatifService);
        $controller->setParametreService($parametreService);
        $controller->setRenduService($renduService);
        $controller->setPropositionTheseService($propositionTheseService);

        $controller->setAdresseSoutenanceForm($adresseForm);
        $controller->setDateLieuForm($dateLieuForm);
        $controller->setMembreForm($membreForm);
        $controller->setLabelEuropeenForm($labelEuropeenForm);
        $controller->setAnglaisForm($anglaisForm);
        $controller->setConfidentialiteForm($confidentialiteForm);
        $controller->setRefusForm($refusForm);
        $controller->setChangementTitreForm($changementTitreForm);

        $controller->setRenderer($renderer);

        $controller->setPropositionTheseAssertion($propositionTheseAssertion);

        /** @var SoutenanceNotificationFactory $soutenanceNotificationFactory */
        $soutenanceNotificationFactory = $container->get(SoutenanceNotificationFactory::class);
        $controller->setSoutenanceNotificationFactory($soutenanceNotificationFactory);

        /** @var TemplateVariablePluginManager $templateVariablePluginManager */
        $templateVariablePluginManager = $container->get(TemplateVariablePluginManager::class);
        $controller->setTemplateVariablePluginManager($templateVariablePluginManager);

        return $controller;
    }
}