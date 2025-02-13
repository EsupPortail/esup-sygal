<?php

namespace Acteur\Controller\ActeurHDR;

use Acteur\Form\ActeurHDR\ActeurHDRForm;
use Acteur\Service\ActeurHDR\ActeurHDRService;
use Application\Service\Role\RoleService;
use Interop\Container\ContainerInterface;

class ActeurHDRControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ActeurHDRController
    {
        $controller = new ActeurHDRController();

        /** @var ActeurHDRService $acteurService */
        $acteurService = $container->get(ActeurHDRService::class);
        $controller->setActeurHDRService($acteurService);

        /** @var RoleService $roleService */
        $roleService = $container->get(RoleService::class);
        $controller->setApplicationRoleService($roleService);

        /** @var \Acteur\Form\ActeurHDR\ActeurHDRForm $acteurForm */
        $acteurForm = $container->get('FormElementManager')->get(ActeurHDRForm::class);
        $controller->setActeurForm($acteurForm);

        return $controller;
    }
}