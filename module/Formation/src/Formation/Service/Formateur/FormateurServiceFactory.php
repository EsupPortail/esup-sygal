<?php

namespace Formation\Service\Formateur;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class FormateurServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return FormateurService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entitymanager
         */
        $entitymanager = $container->get('doctrine.entitymanager.orm_default');

        $service = new FormateurService();
        $service->setEntityManager($entitymanager);
        return $service;
    }
}