<?php

namespace Candidat\Controller;

use Application\Form\MailConfirmationForm;
use Application\Service\MailConfirmationService;
use Candidat\Service\CandidatService;
use Doctorant\Form\MailConsentementForm;
use Interop\Container\ContainerInterface;

class CandidatControllerFactory
{
    public function __invoke(ContainerInterface $container): CandidatController
    {
        /**
         * @var CandidatService $candidatService
         * @var MailConfirmationService $mailConfirmationService
         */
        $candidatService = $container->get(CandidatService::class);
        $mailConfirmationService = $container->get('MailConfirmationService');

        /** @var MailConfirmationForm $mailConfirmationForm */
        $mailConfirmationForm = $container->get('FormElementManager')->get('MailConfirmationForm');

        /** @var MailConsentementForm $mailConsentementForm */
        $mailConsentementForm = $container->get('FormElementManager')->get(MailConsentementForm::class);

        $controller = new CandidatController();
        $controller->setCandidatService($candidatService);
        $controller->setMailConfirmationService($mailConfirmationService);
        $controller->setMailConfirmationForm($mailConfirmationForm);
        $controller->setMailConsentementForm($mailConsentementForm);

        return $controller;
    }
}
