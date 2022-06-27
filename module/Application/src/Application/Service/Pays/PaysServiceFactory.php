<?php

namespace Application\Service\Pays;

use Psr\Container\ContainerInterface;

class PaysServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');

        $service = new PaysService();
        $service->setEntityManager($em);

        return $service;
    }
}