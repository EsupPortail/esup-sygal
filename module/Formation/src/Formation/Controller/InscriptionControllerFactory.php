<?php

namespace Formation\Controller;

use Application\Service\Doctorant\DoctorantService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\File\FileService;
use Application\Service\Individu\IndividuService;
use Doctrine\ORM\EntityManager;
use Formation\Entity\Db\Presence;
use Formation\Service\Inscription\InscriptionService;
use Formation\Service\Presence\PresenceService;
use Interop\Container\ContainerInterface;
use Laminas\View\Renderer\PhpRenderer;

class InscriptionControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return InscriptionController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var DoctorantService $doctorantService
         * @var EtablissementService $etablissementService
         * @var FileService $fileService
         * @var InscriptionService $inscriptionService
         * @var IndividuService $individuService
         * @var PresenceService $presenceService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $doctorantService = $container->get(DoctorantService::class);
        $etablissementService = $container->get(EtablissementService::class);
        $fileService = $container->get(FileService::class);
        $individuService = $container->get(IndividuService::class);
        $inscriptionService = $container->get(InscriptionService::class);
        $presenceService = $container->get(PresenceService::class);

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
        $controller->setPresenceService($presenceService);
        /** forms *****************************************************************************************************/
        /** autres*****************************************************************************************************/
        $controller->setRenderer($renderer);

        return $controller;
    }
}