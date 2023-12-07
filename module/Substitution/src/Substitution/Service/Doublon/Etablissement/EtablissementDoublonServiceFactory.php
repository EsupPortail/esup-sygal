<?php

namespace Substitution\Service\Doublon\Etablissement;

use Psr\Container\ContainerInterface;

class EtablissementDoublonServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EtablissementDoublonService
    {
        $service = new EtablissementDoublonService();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $service->setEntityManager($em);

        return $service;
    }
}