<?php

namespace Soutenance\Controller\Proposition;

use Application\Service\Fichier\FichierService;
use Application\Service\FichierThese\FichierTheseService;
use Application\Service\These\TheseService;
use Application\Service\UserContextService;
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
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Validation\ValidationService;
use Zend\Mvc\Controller\ControllerManager;

class PropositionControllerFactory {

    public function __invoke(ControllerManager $manager)
    {
        /**
         * @var MembreService $membreService
         * @var NotifierSoutenanceService $notificationSoutenanceService
         * @var PropositionService $propositionService
         * @var TheseService $theseService
         * @var UserContextService $userContextService
         * @var ValidationService $validationService
         * @var FichierTheseService $fichierTheseService
         * @var JustificatifService $justificatifService
         */
        $membreService = $manager->getServiceLocator()->get(MembreService::class);
        $notificationSoutenanceService = $manager->getServiceLocator()->get(NotifierSoutenanceService::class);
        $propositionService = $manager->getServiceLocator()->get(PropositionService::class);
        $theseService = $manager->getServiceLocator()->get('TheseService');
        $userContextService = $manager->getServiceLocator()->get('UserContextService');
        $validationService = $manager->getServiceLocator()->get(ValidationService::class);
        $fichierTheseService = $manager->getServiceLocator()->get(FichierTheseService::class);
        $justificatifService = $manager->getServiceLocator()->get(JustificatifService::class);

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
        $dateLieuForm = $manager->getServiceLocator()->get('FormElementManager')->get(DateLieuForm::class);
        $membreForm = $manager->getServiceLocator()->get('FormElementManager')->get(MembreForm::class);
        $labelEuropeenForm = $manager->getServiceLocator()->get('FormElementManager')->get(LabelEuropeenForm::class);
        $anglaisForm = $manager->getServiceLocator()->get('FormElementManager')->get(AnglaisForm::class);
        $confidentialiteForm = $manager->getServiceLocator()->get('FormElementManager')->get(ConfidentialiteForm::class);
        $refusForm = $manager->getServiceLocator()->get('FormElementManager')->get(RefusForm::class);
        $changementTitreForm = $manager->getServiceLocator()->get('FormElementManager')->get(ChangementTitreForm::class);
        $justificatifForm = $manager->getServiceLocator()->get('FormElementManager')->get(JustificatifForm::class);

        /** @var PropositionController $controller */
        $controller = new PropositionController();

        $controller->setMembreService($membreService);
        $controller->setNotifierSoutenanceService($notificationSoutenanceService);
        $controller->setPropositionService($propositionService);
        $controller->setTheseService($theseService);
        $controller->setUserContextService($userContextService);
        $controller->setValidationService($validationService);
        $controller->setFichierTheseService($fichierTheseService);
        $controller->setJustificatifService($justificatifService);

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