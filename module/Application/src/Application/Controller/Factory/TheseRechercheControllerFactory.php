<?php

namespace Application\Controller\Factory;

use Application\Controller\TheseRechercheController;
use Application\Service\Role\RoleService;
use Application\Service\These\TheseSearchService;
use Application\Service\These\TheseService;
use Interop\Container\ContainerInterface;

class TheseRechercheControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return TheseRechercheController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var TheseService            $theseService
         * @var TheseSearchService   $theseSearchService
         * @var RoleService             $roleService
         */
        $theseService = $container->get('TheseService');
        $theseSearchService = $container->get(TheseSearchService::class);
        $roleService = $container->get('RoleService');

        $controller = new TheseRechercheController();
        $controller->setSearchService($theseSearchService);
        $controller->setTheseService($theseService);
        $controller->setRoleService($roleService);

        return $controller;
    }
}