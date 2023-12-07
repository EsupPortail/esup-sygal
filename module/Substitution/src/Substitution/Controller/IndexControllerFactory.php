<?php

namespace Substitution\Controller;

use Psr\Container\ContainerInterface;
use Substitution\Service\Doublon\DoublonService;
use Substitution\Service\Substitution\SubstitutionService;

class IndexControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : IndexController
    {
        $controller = new IndexController();

        /** @var \Substitution\Service\Doublon\DoublonService $doublonService */
        $doublonService = $container->get(DoublonService::class);
        $controller->setDoublonService($doublonService);

        /** @var \Substitution\Service\Substitution\SubstitutionService $substitutionService */
        $substitutionService = $container->get(SubstitutionService::class);
        $controller->setSubstitutionService($substitutionService);

        return $controller;
    }
}