<?php

namespace Substitution\Service\Doublon\Structure;

use Psr\Container\ContainerInterface;

class StructureDoublonServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): StructureDoublonService
    {
        $service = new StructureDoublonService();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $service->setEntityManager($em);

        return $service;
    }
}