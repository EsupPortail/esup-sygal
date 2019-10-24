<?php

namespace Soutenance\Form\ActeurSimule;

use Application\Service\Etablissement\EtablissementService;
use Application\Service\Role\RoleService;
use Zend\Stdlib\Hydrator\HydratorPluginManager;

class ActeurSimuleHydratorFactory {

    public function __invoke(HydratorPluginManager $manager)
    {
        /**
         * @var RoleService $roleService
         * @var EtablissementService $etablissementService
         */
        $roleService = $manager->getServiceLocator()->get('RoleService');
        $etablissementService = $manager->getServiceLocator()->get(EtablissementService::class);

        /** @var ActeurSimuleHydrator $hydrator */
        $hydrator = new ActeurSimuleHydrator();
        $hydrator->setEtablissementService($etablissementService);
        $hydrator->setRoleService($roleService);
        return $hydrator;
    }
}