<?php

namespace UnicaenAvis\Service;

use Psr\Container\ContainerInterface;

class AvisServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AvisService
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');

        $service = new AvisService();
        $service->setObjectManager($em);

        return $service;
    }
}