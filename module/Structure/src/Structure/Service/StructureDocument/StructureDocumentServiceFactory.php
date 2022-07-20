<?php

namespace Structure\Service\StructureDocument;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Interop\Container\ContainerInterface;

class StructureDocumentServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return StructureDocumentService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): StructureDocumentService
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContext
         * @var \Fichier\Service\Fichier\FichierStorageService $fileService
         * @var FichierService $fichierService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContext = $container->get('UserContextService');
        $fileService = $container->get(FichierStorageService::class);
        $fichierService = $container->get('FichierService');

        $service = new StructureDocumentService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContext);
        $service->setFichierStorageService($fileService);;
        $service->setFichierService($fichierService);;

        return $service;
    }
}