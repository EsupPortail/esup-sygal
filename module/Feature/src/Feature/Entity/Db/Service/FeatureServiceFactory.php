<?php

namespace Feature\Entity\Db\Service;

use Psr\Container\ContainerInterface;

class FeatureServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FeatureService
    {
        $service = new FeatureService();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em =  $container->get('doctrine.entitymanager.orm_default');
        $service->setEntityManager($em);

        return $service;
    }
}