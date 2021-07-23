<?php

namespace Formation\Controller;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class IndexControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return IndexController
     */
    public function __invoke(ContainerInterface $container)
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