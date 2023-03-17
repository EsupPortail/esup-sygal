<?php

namespace Horodatage\Service\Horodatage;

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnicaenAuthentification\Service\UserContext;

class HorodatageServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return HorodatageService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : HorodatageService
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContext $userContext
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContext = $container->get(UserContext::class);

        $service = new HorodatageService();
        $service->setObjectManager($entityManager);
        $service->setUserContextService($userContext);
        return $service;
    }
}