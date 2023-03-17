<?php

namespace Application\Form\Factory;

use Application\Form\RoleForm;
use Interop\Container\ContainerInterface;
use UnicaenUtilisateur\Service\Role\RoleService;


class RoleFormFactory {

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : RoleForm
    {
//        /** @var  $hydrator */
//        $hydrator = $container->get('HydratorManager')->get(::class);


        $form = new RoleForm();
//        $serviceRole = $container->get(RoleService::class);
//        $form->setServiceRole($serviceRole);
//        $form->setHydrator($hydrator);

        return $form;
    }
}