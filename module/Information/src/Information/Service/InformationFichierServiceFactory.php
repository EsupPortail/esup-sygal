<?php

namespace Information\Service;

use Application\Service\File\FileService;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

class InformationFichierServiceFactory {
    /**
     * @param ContainerInterface $container
     * @return InformationFichierService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var FileService $fileService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $fileService = $container->get(FileService::class);

        $service = new InformationFichierService();
        $service->setEntityManager($entityManager);
        $service->setFileService($fileService);

        return $service;
    }
}
