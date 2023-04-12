<?php

namespace UnicaenAvis\Hydrator;

use Psr\Container\ContainerInterface;
use UnicaenAvis\Service\AvisService;

class AvisValeurHydratorFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AvisValeurHydrator
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');

        $hydrator = new AvisValeurHydrator($em);

        $avisService = $container->get(AvisService::class);
        $hydrator->setAvisService($avisService);

        return $hydrator;
    }
}