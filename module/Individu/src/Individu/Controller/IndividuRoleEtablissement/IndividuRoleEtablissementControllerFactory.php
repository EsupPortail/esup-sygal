<?php

namespace Individu\Controller\IndividuRoleEtablissement;

use Individu\Service\IndividuRoleEtablissement\IndividuRoleEtablissementService;
use Psr\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;

class IndividuRoleEtablissementControllerFactory {

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : IndividuRoleEtablissementController
    {
        $controller = new IndividuRoleEtablissementController();

        /** @var \Individu\Service\IndividuRole\IndividuRoleService $individuRoleService */
        $individuRoleEtablissementService = $container->get(IndividuRoleEtablissementService::class);
        $controller->setIndividuRoleEtablissementService($individuRoleEtablissementService);

        $etablissementService = $container->get(EtablissementService::class);
        $controller->setEtablissementService($etablissementService);

        return $controller;
    }
}