<?php

namespace Soutenance\Controller;

use Application\Service\Acteur\ActeurService;
use Application\Service\FichierThese\FichierTheseService;
use Application\Service\These\TheseService;
use Application\Service\UserContextService;
use Interop\Container\ContainerInterface;
use Soutenance\Form\Anglais\AnglaisForm;
use Soutenance\Form\ChangementTitre\ChangementTitreForm;
use Soutenance\Form\Confidentialite\ConfidentialiteForm;
use Soutenance\Form\DateLieu\DateLieuForm;
use Soutenance\Form\Justificatif\JustificatifForm;
use Soutenance\Form\LabelEuropeen\LabelEuropeenForm;
use Soutenance\Form\Membre\MembreForm;
use Soutenance\Form\Refus\RefusForm;
use Soutenance\Service\Justificatif\JustificatifService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Notifier\NotifierSoutenanceService;
use Soutenance\Service\Parametre\ParametreService;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Validation\ValidationService;
use Zend\Mvc\Controller\ControllerManager;

class PropositionControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return PropositionController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var ActeurService $acteurService
         * @var MembreService $membreService
         * @var NotifierSoutenanceService $notificationSoutenanceService
         * @var PropositionService $propositionService
         * @var TheseService $theseService
         * @var UserContextService $userContextService
         * @var ValidationService $validationService
         * @var FichierTheseService $fichierTheseService
         * @var JustificatifService $justificatifService
         * @var ParametreService $parametreService
         */
        $acteurService = $container->get(ActeurService::class);
        $membreService = $container->get(MembreService::class);
        $notificationSoutenanceService = $container->get(NotifierSoutenanceService::class);
        $propositionService = $container->get(PropositionService::class);
        $theseService = $container->get('TheseService');
        $userContextService = $container->get('UserContextService');
        $validationService = $container->get(ValidationService::class);
        $fichierTheseService = $container->get(FichierTheseService::class);
        $justificatifService = $container->get(JustificatifService::class);
        $parametreService = $container->get(ParametreService::class);

        /**
         * @var DateLieuForm $dateLieuForm
         * @var MembreForm $membreForm
         * @var LabelEuropeenForm $labelEuropeenForm
         * @var AnglaisForm $anglaisForm
         * @var ConfidentialiteForm $confidentialiteForm
         * @var RefusForm $refusForm
         * @var ChangementTitreForm $changementTitreForm
         * @var JustificatifForm $justificatifForm
         */
        $dateLieuForm = $container->get('FormElementManager')->get(DateLieuForm::class);
        $membreForm = $container->get('FormElementManager')->get(MembreForm::class);
        $labelEuropeenForm = $container->get('FormElementManager')->get(LabelEuropeenForm::class);
        $anglaisForm = $container->get('FormElementManager')->get(AnglaisForm::class);
        $confidentialiteForm = $container->get('FormElementManager')->get(ConfidentialiteForm::class);
        $refusForm = $container->get('FormElementManager')->get(RefusForm::class);
        $changementTitreForm = $container->get('FormElementManager')->get(ChangementTitreForm::class);
        $justificatifForm = $container->get('FormElementManager')->get(JustificatifForm::class);

        /** @var PropositionController $controller */
        $controller = new PropositionController();

        $controller->setActeurService($acteurService);
        $controller->setMembreService($membreService);
        $controller->setNotifierSoutenanceService($notificationSoutenanceService);
        $controller->setPropositionService($propositionService);
        $controller->setTheseService($theseService);
        $controller->setUserContextService($userContextService);
        $controller->setValidationService($validationService);
        $controller->setFichierTheseService($fichierTheseService);
        $controller->setJustificatifService($justificatifService);
        $controller->setParametreService($parametreService);

        $controller->setDateLieuForm($dateLieuForm);
        $controller->setMembreForm($membreForm);
        $controller->setLabelEuropeenForm($labelEuropeenForm);
        $controller->setAnglaisForm($anglaisForm);
        $controller->setConfidentialiteForm($confidentialiteForm);
        $controller->setRefusForm($refusForm);
        $controller->setChangementTitreForm($changementTitreForm);
        $controller->setJustificatifForm($justificatifForm);

        return $controller;
    }
}