<?php

namespace UnicaenAvis\Hydrator;

use Psr\Container\ContainerInterface;
use UnicaenAvis\Service\AvisService;

class AvisTypeHydratorFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AvisTypeHydrator
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');

        $hydrator = new AvisTypeHydrator($em);

        $avisService = $container->get(AvisService::class);
        $hydrator->setAvisService($avisService);

        return $hydrator;
    }
}