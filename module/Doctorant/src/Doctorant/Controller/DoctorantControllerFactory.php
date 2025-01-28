<?php

namespace Doctorant\Controller;

use Application\Form\MailConfirmationForm;
use Application\Service\MailConfirmationService;
use Doctorant\Form\MailConsentementForm;
use Doctorant\Service\DoctorantService;
use Interop\Container\ContainerInterface;
use These\Service\These\TheseService;

class DoctorantControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
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

        /** @var TheseService $theseServuice */
        $theseService = $container->get(TheseService::class);
        $controller->setTheseService($theseService);

        return $controller;
    }
}
