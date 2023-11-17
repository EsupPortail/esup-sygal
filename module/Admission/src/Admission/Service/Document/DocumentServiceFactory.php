<?php

namespace Admission\Service\Document;

use Application\Service\Role\RoleService;
use Application\Service\Source\SourceService;
use Application\Service\UserContextService;
use Application\SourceCodeStringHelper;
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
         * @var RoleService $roleService
         * @var SourceService $sourceService
         * @var UserContextService $userContextService;
         */
        $roleService = $container->get(RoleService::class);
        $sourceService = $container->get(SourceService::class);
        $userContextService = $container->get('UserContextService');
        $fichierService = $container->get(FichierService::class);
        $fichierStorageService = $container->get(FichierStorageService::class);
        /**
         * @var SourceCodeStringHelper $sourceCodeStringHelper;
         */
        $sourceCodeStringHelper = $container->get(SourceCodeStringHelper::class);

        $service = new DocumentService();
//        $service->setEntityManager($entityManager);
        $service->setFichierService($fichierService);
        $service->setFichierStorageService($fichierStorageService);
        $service->setRoleService($roleService);
        $service->setSourceService($sourceService);
        $service->setUserContextService($userContextService);
        $service->setSourceCodeStringHelper($sourceCodeStringHelper);

        return $service;
    }
}