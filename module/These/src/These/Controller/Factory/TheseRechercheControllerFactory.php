<?php

namespace These\Controller\Factory;

use Application\Service\Role\RoleService;
use Interop\Container\ContainerInterface;
use These\Controller\TheseRechercheController;
use These\Service\These\TheseSearchService;
use These\Service\These\TheseService;

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
        $controller->setApplicationRoleService($roleService);

        return $controller;
    }
}