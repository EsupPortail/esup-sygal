<?php

namespace Information\Service;

use Application\Service\UserContextService;
use Doctrine\ORM\EntityManager;
use Information\Service\InformationLangue\InformationLangueService;
use Interop\Container\ContainerInterface;

class InformationServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): InformationService
    {
        /**
         * @var EntityManager $entityManager
         * @var UserContextService $userContextService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userContextService = $container->get(\UnicaenAuthentification\Service\UserContext::class);

        $service = new InformationService();
        $service->setEntityManager($entityManager);
        $service->setUserContextService($userContextService);

        /** @var InformationLangueService $informationLangueService */
        $informationLangueService = $container->get(InformationLangueService::class);
        $service->setInformationLangueService($informationLangueService);

        return $service;
    }
}
