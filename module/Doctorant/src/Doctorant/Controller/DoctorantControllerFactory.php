<?php

namespace Doctorant\Controller;

use Application\Form\MailConfirmationForm;
use Application\Service\MailConfirmationService;
use Doctorant\Form\MailConsentementForm;
use Doctorant\Service\DoctorantService;
use Interop\Container\ContainerInterface;

class DoctorantControllerFactory
{
    public function __invoke(ContainerInterface $container): DoctorantController
    {
        /**
         * @var DoctorantService $doctorantService
         * @var MailConfirmationService $mailConfirmationService
         */
        $doctorantService = $container->get('DoctorantService');
        $mailConfirmationService = $container->get('MailConfirmationService');

        /** @var MailConfirmationForm $mailConfirmationForm */
        $mailConfirmationForm = $container->get('FormElementManager')->get('MailConfirmationForm');

        /** @var MailConsentementForm $mailConsentementForm */
        $mailConsentementForm = $container->get('FormElementManager')->get(MailConsentementForm::class);

        $controller = new DoctorantController();
        $controller->setDoctorantService($doctorantService);
        $controller->setMailConfirmationService($mailConfirmationService);
        $controller->setMailConfirmationForm($mailConfirmationForm);
        $controller->setMailConsentementForm($mailConsentementForm);

        return $controller;
    }
}
