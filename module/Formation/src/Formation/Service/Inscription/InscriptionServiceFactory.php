<?php

namespace Formation\Service\Inscription;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class InscriptionServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return InscriptionService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        $service = new InscriptionService();
        $service->setEntityManager($entitymanager);
        return $service;
    }
}