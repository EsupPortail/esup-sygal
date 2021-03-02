<?php

namespace ComiteSuivi\Service\CompteRendu;

use Application\Service\Fichier\FichierService;
use Application\Service\FichierThese\FichierTheseService;
use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class CompteRenduServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return CompteRenduService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         * @var FichierService $fichierService
         * @var FichierTheseService $fichierTheseService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContextService = $container->get('authUserContext');
        $fichierService             = $container->get('FichierService');
        $fichierTheseService        = $container->get('FichierTheseService');

        $service = new CompteRenduService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);
        $service->setFichierTheseService($fichierTheseService);
        $service->setFichierService($fichierService);

        return $service;

    }
}