<?php

namespace Soutenance\Service\Avis;

use Fichier\Service\Fichier\FichierService;
use These\Service\FichierThese\FichierTheseService;
use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class AvisServiceFactory
{
    /**
     * @param ContainerInterface $container
     * @return AvisService
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
        $userContextService = $container->get('UserContextService');
        $fichierService             = $container->get('FichierService');
        $fichierTheseService        = $container->get('FichierTheseService');

        /** @var AvisService $service */
        $service = new AvisService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);
        $service->setFichierService($fichierService);
        $service->setFichierTheseService($fichierTheseService);

        return $service;
    }
}
