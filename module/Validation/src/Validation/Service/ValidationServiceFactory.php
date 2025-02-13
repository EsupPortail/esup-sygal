<?php

namespace Validation\Service;

use Psr\Container\ContainerInterface;

class ValidationServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ValidationService
    {
        $service = new ValidationService();

        $em = $container->get('doctrine.entitymanager.orm_default');
        $service->setEntityManager($em);

        /** @var \Application\Service\UserContextService $userContext */
        $userContext = $container->get(\UnicaenAuthentification\Service\UserContext::class);
        $service->setUserContextService($userContext);

        return $service;
    }
}