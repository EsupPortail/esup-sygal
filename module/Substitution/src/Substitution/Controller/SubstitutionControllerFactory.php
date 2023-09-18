<?php

namespace Substitution\Controller;

use Psr\Container\ContainerInterface;
use Substitution\Service\SubstitutionService;

class SubstitutionControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : SubstitutionController
    {
        $controller = new SubstitutionController();

        /** @var \Substitution\Service\SubstitutionService $substitutionService */
        $substitutionService = $container->get(SubstitutionService::class);
        $controller->setSubstitutionService($substitutionService);

        return $controller;
    }
}