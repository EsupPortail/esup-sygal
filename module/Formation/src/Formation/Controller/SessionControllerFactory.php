<?php

namespace Formation\Controller;

use Application\Service\AnneeUniv\AnneeUnivService;
use Fichier\Service\Fichier\FichierStorageService;
use Formation\Service\Formation\FormationService;
use Formation\Service\Notification\FormationNotificationFactory;
use Formation\Service\Presence\PresenceService;
use Formation\Service\SessionStructureValide\SessionStructureValideService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Etablissement\EtablissementService;
use Doctrine\ORM\EntityManager;
use Formation\Form\Session\SessionForm;
use Formation\Service\Inscription\InscriptionService;
use Notification\Service\NotifierService;
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
         * @var \Fichier\Service\Fichier\FichierStorageService $fichierStorageService
         * @var FormationService $formationService
         * @var InscriptionService $inscriptionService
         * @var NotifierService $notificationService
         * @var PresenceService $presenceService
         * @var SessionService $sessionService
         * @var SessionStructureValideService $sessionStructureComplementaireService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $etablissementService = $container->get(EtablissementService::class);
        $fichierStorageService = $container->get(FichierStorageService::class);
        $formationService = $container->get(FormationService::class);
        $inscriptionService = $container->get(InscriptionService::class);
        $notificationService = $container->get(NotifierService::class);
        $presenceService = $container->get(PresenceService::class);
        $sessionService = $container->get(SessionService::class);
        $sessionStructureComplementaireService = $container->get(SessionStructureValideService::class);
        $anneeUnivService = $container->get(AnneeUnivService::class);

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
        $controller->setFichierStorageService($fichierStorageService);
        $controller->setFormationService($formationService);
        $controller->setInscriptionService($inscriptionService);
        $controller->setNotifierService($notificationService);
        $controller->setPresenceService($presenceService);
        $controller->setSessionService($sessionService);
        $controller->setSessionStructureValideService($sessionStructureComplementaireService);
        $controller->setAnneeUnivService($anneeUnivService);
        /** Form ******************************************************************************************************/
        $controller->setSessionForm($sessionForm);
        /** Autre *****************************************************************************************************/
        $controller->setRenderer($renderer);

        /** @var \Formation\Service\Notification\FormationNotificationFactory $formationNotificationFactory */
        $formationNotificationFactory = $container->get(FormationNotificationFactory::class);
        $controller->setFormationNotificationFactory($formationNotificationFactory);

        return $controller;
    }
}