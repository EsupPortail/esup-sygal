<?php

namespace Individu\Service;

use Psr\Container\ContainerInterface;

trait IndividuServiceLocateTrait
{
    /**
     * @param ContainerInterface $container
     * @return IndividuService
     */
    public function locateIndividuService(ContainerInterface $container)
    {
        /** @var IndividuService $service */
        $service = $container->get(IndividuService::class);

        return $service;
    }
}