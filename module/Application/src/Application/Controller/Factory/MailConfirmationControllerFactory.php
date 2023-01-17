<?php

namespace Application\Controller\Factory;

use Application\Controller\MailConfirmationController;
use Application\Form\MailConfirmationForm;
use Application\Service\Notification\ApplicationNotificationFactory;
use Individu\Service\IndividuService;
use Application\Service\MailConfirmationService;
use Notification\Service\NotifierService;
use Interop\Container\ContainerInterface;

class MailConfirmationControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): MailConfirmationController
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
        $controller->setNotifierService($notifierService);
        $controller->setMailConfirmationForm($mailConfirmationForm);

        /** @var \Application\Service\Notification\ApplicationNotificationFactory $applicationNotificationFactory */
        $applicationNotificationFactory = $container->get(ApplicationNotificationFactory::class);
        $controller->setApplicationNotificationFactory($applicationNotificationFactory);

        return $controller;
    }
}