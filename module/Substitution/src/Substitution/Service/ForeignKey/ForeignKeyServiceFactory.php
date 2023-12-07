<?php

namespace Substitution\Service\ForeignKey;

use Psr\Container\ContainerInterface;
use Substitution\Service\ForeignKey\ForeignKeyService;

class ForeignKeyServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ForeignKeyService
    {
        $service = new ForeignKeyService;

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $service->setEntityManager($em);

        return $service;
    }
}