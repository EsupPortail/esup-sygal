<?php

namespace Soutenance\Service\QualiteLibelleSupplementaire;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class QualiteLibelleSupplementaireServiceFactory
{

    /**
     * @param ContainerInterface $container
     * @return QualiteLibelleSupplementaireService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContext
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContext = $container->get('UserContextService');

        /**
         * @var QualiteLibelleSupplementaireService $service
         */
        $service = new QualiteLibelleSupplementaireService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContext);
        return $service;

    }
}