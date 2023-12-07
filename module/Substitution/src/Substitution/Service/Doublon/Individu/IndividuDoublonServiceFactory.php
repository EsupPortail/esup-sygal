<?php

namespace Substitution\Service\Doublon\Individu;

use Psr\Container\ContainerInterface;

class IndividuDoublonServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): IndividuDoublonService
    {
        $service = new IndividuDoublonService();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $service->setEntityManager($em);

        return $service;
    }
}