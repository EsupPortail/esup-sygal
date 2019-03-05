<?php

namespace Soutenance\Controller\Proposition;

use Application\Service\These\TheseService;
use Application\Service\UserContextService;
use Soutenance\Form\Confidentialite\ConfigurationForm;
use Soutenance\Form\DateLieu\DateLieuForm;
use Soutenance\Form\LabelEtAnglais\LabelEtAnglaisForm;
use Soutenance\Form\Membre\MembreForm;
use Soutenance\Form\Refus\RefusForm;
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
         */
        $membreService = $manager->getServiceLocator()->get(MembreService::class);
        $notificationSoutenanceService = $manager->getServiceLocator()->get(NotifierSoutenanceService::class);
        $propositionService = $manager->getServiceLocator()->get(PropositionService::class);
        $theseService = $manager->getServiceLocator()->get('TheseService');
        $userContextService = $manager->getServiceLocator()->get('UserContextService');
        $validationService = $manager->getServiceLocator()->get(ValidationService::class);

        /**
         * @var DateLieuForm $dateLieuForm
         * @var MembreForm $membreForm
         * @var LabelEtAnglaisForm $labelEtAnglaisForm
         * @var ConfigurationForm $confidentialiteForm
         * @var RefusForm $refusForm
         */
        $dateLieuForm = $manager->getServiceLocator()->get('FormElementManager')->get(DateLieuForm::class);
        $membreForm = $manager->getServiceLocator()->get('FormElementManager')->get(MembreForm::class);
        $labelEtAnglaisForm = $manager->getServiceLocator()->get('FormElementManager')->get(LabelEtAnglaisForm::class);
        $confidentialiteForm = $manager->getServiceLocator()->get('FormElementManager')->get(ConfigurationForm::class);
        $refusForm = $manager->getServiceLocator()->get('FormElementManager')->get(RefusForm::class);

        /** @var PropositionController $controller */
        $controller = new PropositionController();

        $controller->setMembreService($membreService);
        $controller->setNotifierSoutenanceService($notificationSoutenanceService);
        $controller->setPropositionService($propositionService);
        $controller->setTheseService($theseService);
        $controller->setUserContextService($userContextService);
        $controller->setValidationService($validationService);

        $controller->setDateLieuForm($dateLieuForm);
        $controller->setMembreForm($membreForm);
        $controller->setLabelEtAnglaisForm($labelEtAnglaisForm);
        $controller->setConfidentialiteForm($confidentialiteForm);
        $controller->setRefusForm($refusForm);

        return $controller;
    }
}