<?php

namespace Application\Service\StructureDocument;

use Application\Service\Fichier\FichierService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Application\Service\UserContextService;

class StructureDocumentServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return StructureDocumentService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContext
         * @var FichierService $fichierService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContext = $container->get('UserContextService');
        $fichierService = $container->get('FichierService');

        $service = new StructureDocumentService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContext);
        $service->setFichierService($fichierService);;

        return $service;
    }
}