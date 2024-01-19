<?php

namespace These\Controller\Factory;

use Application\Service\Role\RoleService;
use Interop\Container\ContainerInterface;
use These\Controller\ActeurController;
use These\Form\Acteur\ActeurForm;
use These\Service\Acteur\ActeurService;

class ActeurControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ActeurController
    {
        $controller = new ActeurController();

        /** @var ActeurService $acteurService */
        $acteurService = $container->get(ActeurService::class);
        $controller->setActeurService($acteurService);

        /** @var RoleService $roleService */
        $roleService = $container->get(RoleService::class);
        $controller->setRoleService($roleService);

        /** @var \These\Form\Acteur\ActeurForm $acteurForm */
        $acteurForm = $container->get('FormElementManager')->get(ActeurForm::class);
        $controller->setActeurForm($acteurForm);

        return $controller;
    }
}