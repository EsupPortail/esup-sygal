<?php

namespace Formation\Controller;

use Fichier\Service\Fichier\FichierStorageService;
use Formation\Service\Session\SessionService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Etablissement\EtablissementService;
use Individu\Service\IndividuService;
use Structure\Service\StructureDocument\StructureDocumentService;
use Doctorant\Service\DoctorantService;
use Doctrine\ORM\EntityManager;
use Formation\Service\Inscription\InscriptionService;
use Formation\Service\Notification\NotifierService;
use Formation\Service\Presence\PresenceService;
use Interop\Container\ContainerInterface;
use Laminas\View\Renderer\PhpRenderer;

class InscriptionControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return InscriptionController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : InscriptionController
    {
        /**
         * @var EntityManager $entityManager
         * @var DoctorantService $doctorantService
         * @var EtablissementService $etablissementService
         * @var \Fichier\Service\Fichier\FichierStorageService $fichierStorageService
         * @var InscriptionService $inscriptionService
         * @var IndividuService $individuService
         * @var NotifierService $notificationService
         * @var PresenceService $presenceService
         * @var SessionService $sessionService
         * @var StructureDocumentService $structureDocumentService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $doctorantService = $container->get(DoctorantService::class);
        $etablissementService = $container->get(EtablissementService::class);
        $fichierStorageService = $container->get(FichierStorageService::class);
        $individuService = $container->get(IndividuService::class);
        $inscriptionService = $container->get(InscriptionService::class);
        $notificationService = $container->get(NotifierService::class);
        $presenceService = $container->get(PresenceService::class);
        $sessionService = $container->get(SessionService::class);
        $structureDocumentService = $container->get(StructureDocumentService::class);

        /* @var $renderer PhpRenderer */
        $renderer = $container->get('ViewRenderer');

        $controller = new InscriptionController();
        /** services **************************************************************************************************/
        $controller->setEntityManager($entityManager);
        $controller->setDoctorantService($doctorantService);
        $controller->setEtablissementService($etablissementService);
        $controller->setFichierStorageService($fichierStorageService);
        $controller->setIndividuService($individuService);
        $controller->setInscriptionService($inscriptionService);
        $controller->setNotifierService($notificationService);
        $controller->setPresenceService($presenceService);
        $controller->setSessionService($sessionService);
        $controller->setStructureDocumentService($structureDocumentService);
        /** forms *****************************************************************************************************/
        /** autres*****************************************************************************************************/
        $controller->setRenderer($renderer);

        return $controller;
    }
}