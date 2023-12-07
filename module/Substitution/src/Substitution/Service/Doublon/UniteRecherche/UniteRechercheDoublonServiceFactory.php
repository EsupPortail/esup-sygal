<?php

namespace Substitution\Service\Doublon\UniteRecherche;

use Psr\Container\ContainerInterface;

class UniteRechercheDoublonServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): UniteRechercheDoublonService
    {
        $service = new UniteRechercheDoublonService();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $service->setEntityManager($em);

        return $service;
    }
}