<?php

namespace Formation\Controller;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IndexControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return IndexController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : IndexController
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $controller = new IndexController();
        $controller->setEntityManager($entityManager);
        return $controller;
    }

}