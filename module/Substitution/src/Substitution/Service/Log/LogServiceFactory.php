<?php

namespace Substitution\Service\Log;

use Psr\Container\ContainerInterface;
use Substitution\Service\Log\LogService;

class LogServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): LogService
    {
        $service = new LogService;

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $service->setEntityManager($em);

        return $service;
    }
}