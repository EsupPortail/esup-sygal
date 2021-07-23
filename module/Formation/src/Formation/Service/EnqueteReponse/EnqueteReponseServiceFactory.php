<?php

namespace Formation\Service\EnqueteReponse;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class EnqueteReponseServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return EnqueteReponseService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        $service = new EnqueteReponseService();
        $service->setEntityManager($entitymanager);
        return $service;
    }
}