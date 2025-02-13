<?php

namespace Acteur\Controller\ActeurThese;

use Acteur\Form\ActeurThese\ActeurTheseForm;
use Acteur\Service\ActeurThese\ActeurTheseService;
use Application\Service\Role\RoleService;
use Interop\Container\ContainerInterface;

class ActeurTheseControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ActeurTheseController
    {
        $controller = new ActeurTheseController();

        /** @var ActeurTheseService $acteurService */
        $acteurService = $container->get(ActeurTheseService::class);
        $controller->setActeurTheseService($acteurService);

        /** @var RoleService $roleService */
        $roleService = $container->get(RoleService::class);
        $controller->setApplicationRoleService($roleService);

        /** @var \Acteur\Form\ActeurThese\ActeurTheseForm $acteurForm */
        $acteurForm = $container->get('FormElementManager')->get(ActeurTheseForm::class);
        $controller->setActeurForm($acteurForm);

        return $controller;
    }
}