<?php

namespace Substitution\Service\Trigger;

use Psr\Container\ContainerInterface;
use Substitution\Service\Trigger\TriggerService;

class TriggerServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TriggerService
    {
        $service = new TriggerService;

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $service->setEntityManager($em);

        return $service;
    }
}