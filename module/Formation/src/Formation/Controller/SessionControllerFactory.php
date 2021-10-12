<?php

namespace Formation\Controller;

use Application\Service\Etablissement\EtablissementService;
use Application\Service\File\FileService;
use Doctrine\ORM\EntityManager;
use Formation\Form\Session\SessionForm;
use Formation\Service\Inscription\InscriptionService;
use Formation\Service\Notification\NotificationService;
use Formation\Service\Session\SessionService;
use Interop\Container\ContainerInterface;
use Laminas\View\Renderer\PhpRenderer;

class SessionControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return SessionController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var EtablissementService $etablissementService
         * @var FileService $fileService
         * @var InscriptionService $inscriptionService
         * @var SessionService $sessionService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $etablissementService = $container->get(EtablissementService::class);
        $fileService = $container->get(FileService::class);
        $inscriptionService = $container->get(InscriptionService::class);
        $notificationService = $container->get(NotificationService::class);
        $sessionService = $container->get(SessionService::class);

        /**
         * @var SessionForm $sessionForm
         */
        $sessionForm = $container->get('FormElementManager')->get(SessionForm::class);

        /* @var $renderer PhpRenderer */
        $renderer = $container->get('ViewRenderer');

        $controller = new SessionController();
        $controller->setEntityManager($entityManager);
        /** Service ***************************************************************************************************/
        $controller->setEtablissementService($etablissementService);
        $controller->setFileService($fileService);
        $controller->setInscriptionService($inscriptionService);
        $controller->setNotificationService($notificationService);
        $controller->setSessionService($sessionService);
        /** Form ******************************************************************************************************/
        $controller->setSessionForm($sessionForm);
        /** Autre *****************************************************************************************************/
        $controller->setRenderer($renderer);

        return $controller;
    }
}