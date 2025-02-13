<?php

namespace HDR\Controller\Factory;

use Application\Service\Role\RoleService;
use HDR\Controller\HDRRechercheController;
use HDR\Service\HDRSearchService;
use HDR\Service\HDRService;
use Interop\Container\ContainerInterface;

class HDRRechercheControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return HDRRechercheController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var HDRService            $hdrService
         * @var HDRSearchService   $hdrSearchService
         * @var RoleService             $roleService
         */
        $hdrService = $container->get(HDRService::class);
        $hdrSearchService = $container->get(HDRSearchService::class);
        $roleService = $container->get('RoleService');

        $controller = new HDRRechercheController();
        $controller->setSearchService($hdrSearchService);
        $controller->setHDRService($hdrService);
        $controller->setApplicationRoleService($roleService);

        return $controller;
    }
}