<?php

namespace UnicaenAvis\Hydrator;

use Psr\Container\ContainerInterface;
use UnicaenAvis\Service\AvisService;

class AvisHydratorFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AvisHydrator
    {
        $hydrator = new AvisHydrator();

        $avisService = $container->get(AvisService::class);
        $hydrator->setAvisService($avisService);

        return $hydrator;
    }
}