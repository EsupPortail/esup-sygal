<?php

namespace InscriptionAdministrative\Controller;

use InscriptionAdministrative\Service\InscriptionAdministrativeService;
use Psr\Container\ContainerInterface;

class InscriptionAdministrativeControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): InscriptionAdministrativeController
    {
        $controller = new InscriptionAdministrativeController();

        /** @var \InscriptionAdministrative\Service\InscriptionAdministrativeService $inscriptionAdministrativeService */
        $inscriptionAdministrativeService = $container->get(InscriptionAdministrativeService::class);
        $controller->setInscriptionAdministrativeService($inscriptionAdministrativeService);

        return $controller;
    }
}