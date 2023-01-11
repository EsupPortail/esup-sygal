<?php

namespace Application\Controller\Factory;

use Application\Controller\MailConfirmationController;
use Application\Form\MailConfirmationForm;
use Individu\Service\IndividuService;
use Application\Service\MailConfirmationService;
use Application\Service\Notification\NotifierService;
use Interop\Container\ContainerInterface;

class MailConfirmationControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return MailConfirmationController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var MailConfirmationService $mailConfirmationService
         * @var NotifierService $notifierService
         * @var IndividuService $individuService
         */
        $mailConfirmationService = $container->get('MailConfirmationService');
        $notifierService = $container->get(NotifierService::class);
        $individuService = $container->get(IndividuService::class);

        /** @var MailConfirmationForm $mailConfirmationForm */
        $mailConfirmationForm = $container->get('FormElementManager')->get('MailConfirmationForm');

        $controller = new MailConfirmationController();
        $controller->setMailConfirmationService($mailConfirmationService);
        $controller->setIndividuService($individuService);
        $controller->setApplicationNotifierService($notifierService);
        $controller->setMailConfirmationForm($mailConfirmationForm);

        return $controller;
    }
}