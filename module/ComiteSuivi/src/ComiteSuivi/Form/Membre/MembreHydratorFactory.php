<?php

namespace ComiteSuivi\Form\Membre;

use Application\Service\Role\RoleService;
use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviService;
use Zend\Stdlib\Hydrator\HydratorPluginManager;

class MembreHydratorFactory {

    /**
     * @param HydratorPluginManager $manager
     * @return MembreHydrator
     */
    public function __invoke(HydratorPluginManager $manager)
    {
        /**
         * @var ComiteSuiviService $comiteSuiviService
         * @var RoleService $roleService
         */
        $comiteSuiviService = $manager->getServiceLocator()->get(ComiteSuiviService::class);
        $roleService = $manager->getServiceLocator()->get(RoleService::class);

        /** @var MembreHydrator $hydrator */
        $hydrator = new MembreHydrator();
        $hydrator->setComiteSuiviService($comiteSuiviService);
        $hydrator->setRoleService($roleService);
        return $hydrator;
    }
}