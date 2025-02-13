<?php

namespace Soutenance\Service\Avis;

use Acteur\Service\ActeurHDR\ActeurHDRService;
use Acteur\Service\ActeurThese\ActeurTheseService;
use Depot\Service\FichierHDR\FichierHDRService;
use Fichier\Service\Fichier\FichierService;
use Depot\Service\FichierThese\FichierTheseService;
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
         * @var FichierHDRService $fichierHDRService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContextService = $container->get('UserContextService');
        $fichierService             = $container->get('FichierService');
        $fichierTheseService        = $container->get('FichierTheseService');
        $fichierHDRService        = $container->get(FichierHDRService::class);

        /** @var AvisService $service */
        $service = new AvisService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);
        $service->setFichierService($fichierService);
        $service->setFichierTheseService($fichierTheseService);
        $service->setFichierHDRService($fichierHDRService);

        /** @var ActeurTheseService $acteurService */
        $acteurService = $container->get(ActeurTheseService::class);
        $service->setActeurTheseService($acteurService);

        /** @var ActeurHDRService $acteurService */
        $acteurService = $container->get(ActeurHDRService::class);
        $service->setActeurHDRService($acteurService);

        return $service;
    }
}
