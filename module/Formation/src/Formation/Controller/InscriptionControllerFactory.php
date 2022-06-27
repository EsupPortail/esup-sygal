<?php

namespace Formation\Controller;

use Structure\Service\Etablissement\EtablissementService;
use Application\Service\File\FileService;
use Individu\Service\IndividuService;
use Structure\Service\StructureDocument\StructureDocumentService;
use Doctorant\Service\DoctorantService;
use Doctrine\ORM\EntityManager;
use Formation\Service\Inscription\InscriptionService;
use Formation\Service\Notification\NotificationService;
use Formation\Service\Presence\PresenceService;
use Interop\Container\ContainerInterface;
use Laminas\View\Renderer\PhpRenderer;

class InscriptionControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return InscriptionController
     */
    public function __invoke(ContainerInterface $container) : InscriptionController
    {
        /**
         * @var EntityManager $entityManager
         * @var DoctorantService $doctorantService
         * @var EtablissementService $etablissementService
         * @var FileService $fileService
         * @var InscriptionService $inscriptionService
         * @var IndividuService $individuService
         * @var NotificationService $notificationService
         * @var PresenceService $presenceService
         * @var StructureDocumentService $structureDocumentService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $doctorantService = $container->get(DoctorantService::class);
        $etablissementService = $container->get(EtablissementService::class);
        $fileService = $container->get(FileService::class);
        $individuService = $container->get(IndividuService::class);
        $inscriptionService = $container->get(InscriptionService::class);
        $notificationService = $container->get(NotificationService::class);
        $presenceService = $container->get(PresenceService::class);
        $structureDocumentService = $container->get(StructureDocumentService::class);

        /* @var $renderer PhpRenderer */
        $renderer = $container->get('ViewRenderer');

        $controller = new InscriptionController();
        /** services **************************************************************************************************/
        $controller->setEntityManager($entityManager);
        $controller->setDoctorantService($doctorantService);
        $controller->setEtablissementService($etablissementService);
        $controller->setFileService($fileService);
        $controller->setIndividuService($individuService);
        $controller->setInscriptionService($inscriptionService);
        $controller->setNotificationService($notificationService);
        $controller->setPresenceService($presenceService);
        $controller->setStructureDocumentService($structureDocumentService);
        /** forms *****************************************************************************************************/
        /** autres*****************************************************************************************************/
        $controller->setRenderer($renderer);

        return $controller;
    }
}