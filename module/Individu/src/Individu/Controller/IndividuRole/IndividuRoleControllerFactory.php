<?php

namespace Individu\Controller\IndividuRole;

use Individu\Form\IndividuRole\IndividuRoleForm;
use Individu\Service\IndividuRole\IndividuRoleService;
use Psr\Container\ContainerInterface;

class IndividuRoleControllerFactory {

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : IndividuRoleController
    {
        $controller = new IndividuRoleController();

        /** @var \Individu\Service\IndividuRole\IndividuRoleService $individuRoleService */
        $individuRoleService = $container->get(IndividuRoleService::class);
        $controller->setIndividuRoleService($individuRoleService);

        /** @var \Individu\Form\IndividuRole\IndividuRoleForm $form */
        $form = $container->get('FormElementManager')->get(IndividuRoleForm::class);
        $controller->setForm($form);

        return $controller;
    }
}