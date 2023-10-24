<?php

namespace Admission\Service\Document;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class DocumentServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return DocumentService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DocumentService
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContext
         * @var FichierStorageService $fileService
         * @var FichierService $fichierService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContext = $container->get(UserContextService::class);
        $fileService = $container->get(FichierStorageService::class);
        $fichierService = $container->get(FichierService::class);

        $service = new DocumentService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContext);
        $service->setFichierStorageService($fileService);;
        $service->setFichierService($fichierService);;

        return $service;
    }
}