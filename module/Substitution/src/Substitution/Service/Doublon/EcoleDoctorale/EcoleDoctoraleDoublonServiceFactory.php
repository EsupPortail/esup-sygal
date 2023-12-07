<?php

namespace Substitution\Service\Doublon\EcoleDoctorale;

use Psr\Container\ContainerInterface;

class EcoleDoctoraleDoublonServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EcoleDoctoraleDoublonService
    {
        $service =  new EcoleDoctoraleDoublonService();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $service->setEntityManager($em);

        return $service;
    }
}