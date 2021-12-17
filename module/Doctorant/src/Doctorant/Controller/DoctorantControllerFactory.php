<?php

namespace Doctorant\Controller;

use Application\Form\MailConfirmationForm;
use Application\Service\MailConfirmationService;
use Doctorant\Service\DoctorantService;
use Interop\Container\ContainerInterface;

class DoctorantControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return DoctorantController
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

        $controller = new DoctorantController();
        $controller->setDoctorantService($doctorantService);
        $controller->setMailConfirmationService($mailConfirmationService);
        $controller->setMailConfirmationForm($mailConfirmationForm);

        return $controller;
    }
}
