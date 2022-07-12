<?php

namespace Formation\Controller;

use Formation\Service\Formation\FormationService;
use Formation\Service\Presence\PresenceService;
use Formation\Service\SessionStructureValide\SessionStructureValideService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Etablissement\EtablissementService;
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : SessionController
    {
        /**
         * @var EntityManager $entityManager
         * @var EtablissementService $etablissementService
         * @var FileService $fileService
         * @var FormationService $formationService
         * @var InscriptionService $inscriptionService
         * @var NotificationService $notificationService
         * @var PresenceService $presenceService
         * @var SessionService $sessionService
         * @var SessionStructureValideService $sessionStructureComplementaireService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $etablissementService = $container->get(EtablissementService::class);
        $fileService = $container->get(FileService::class);
        $formationService = $container->get(FormationService::class);
        $inscriptionService = $container->get(InscriptionService::class);
        $notificationService = $container->get(NotificationService::class);
        $presenceService = $container->get(PresenceService::class);
        $sessionService = $container->get(SessionService::class);
        $sessionStructureComplementaireService = $container->get(SessionStructureValideService::class);

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
        $controller->setFormationService($formationService);
        $controller->setInscriptionService($inscriptionService);
        $controller->setNotificationService($notificationService);
        $controller->setPresenceService($presenceService);
        $controller->setSessionService($sessionService);
        $controller->setSessionStructureValideService($sessionStructureComplementaireService);
        /** Form ******************************************************************************************************/
        $controller->setSessionForm($sessionForm);
        /** Autre *****************************************************************************************************/
        $controller->setRenderer($renderer);

        return $controller;
    }
}