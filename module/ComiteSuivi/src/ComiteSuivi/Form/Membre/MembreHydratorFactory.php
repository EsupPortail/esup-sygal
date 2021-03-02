<?php

namespace ComiteSuivi\Form\Membre;

use Application\Service\Role\RoleService;
use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviService;
use Interop\Container\ContainerInterface;

class MembreHydratorFactory {

    /**
     * @param ContainerInterface $manager
     * @return MembreHydrator
     */
    public function __invoke(ContainerInterface $manager)
    {
        /**
         * @var ComiteSuiviService $comiteSuiviService
         * @var RoleService $roleService
         */
        $comiteSuiviService = $manager->get(ComiteSuiviService::class);
        $roleService = $manager->get(RoleService::class);

        $hydrator = new MembreHydrator();
        $hydrator->setComiteSuiviService($comiteSuiviService);
        $hydrator->setRoleService($roleService);
        return $hydrator;
    }
}