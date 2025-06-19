<?php

namespace Formation\Service\Parametre;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ParametreServiceFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : ParametreService
    {
        /**
         * @var \UnicaenParametre\Service\Parametre\ParametreService $parametreService
         */
        $parametreService = $container->get(\UnicaenParametre\Service\Parametre\ParametreService::class);

        $service = new ParametreService();
        $service->setParametreService($parametreService);

        return $service;
    }
}