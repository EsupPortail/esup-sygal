<?php

namespace Application\Controller\Factory;

use Application\Controller\PaysController;
use Application\Controller\RoleController;
use Structure\Service\Etablissement\EtablissementService;
use Application\Service\Role\RoleService;
use Application\Service\Pays\PaysService;
use Interop\Container\ContainerInterface;

class PaysControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return PaysController
     */
    public function __invoke(ContainerInterface $container) : PaysController
    {
        /**
         * @var \Application\Service\Pays\PaysService $paysService
         */
        $paysService = $container->get(PaysService::class);
        $controller = new PaysController();
        $controller->setPaysService($paysService);
        return $controller;
    }
}