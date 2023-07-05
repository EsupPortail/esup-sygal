<?php

namespace InscriptionAdministrative\Service;

use Psr\Container\ContainerInterface;

class InscriptionAdministrativeServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): InscriptionAdministrativeService
        {
            /** @var \Doctrine\ORM\EntityManager $em */
            $em = $container->get('doctrine.entitymanager.orm_default');

            $service = new InscriptionAdministrativeService();
            $service->setEntityManager($em);

            return $service;
        }
}