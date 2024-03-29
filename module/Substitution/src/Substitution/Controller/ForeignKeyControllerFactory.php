<?php

namespace Substitution\Controller;

use Psr\Container\ContainerInterface;
use Substitution\Service\ForeignKey\ForeignKeyService;

class ForeignKeyControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : ForeignKeyController
    {
        $controller = new ForeignKeyController();

        /** @var \Substitution\Service\ForeignKey\ForeignKeyService $foreignKeyService */
        $foreignKeyService = $container->get(ForeignKeyService::class);
        $controller->setForeignKeyService($foreignKeyService);

        return $controller;
    }
}