<?php

namespace Soutenance\Form\ActeurSimule;

use Application\Service\Etablissement\EtablissementService;
use Application\Service\Individu\IndividuService;
use Application\Service\Role\RoleService;
use Zend\Stdlib\Hydrator\HydratorPluginManager;

class ActeurSimuleHydratorFactory {

    public function __invoke(HydratorPluginManager $manager)
    {
        /**
         * @var RoleService $roleService
         * @var IndividuService $individuService
         * @var EtablissementService $etablissementService
         */
        $roleService = $manager->getServiceLocator()->get('RoleService');
        $individuService = $manager->getServiceLocator()->get('IndividuService');
        $etablissementService = $manager->getServiceLocator()->get(EtablissementService::class);

        /** @var ActeurSimuleHydrator $hydrator */
        $hydrator = new ActeurSimuleHydrator();
        $hydrator->setEtablissementService($etablissementService);
        $hydrator->setRoleService($roleService);
        $hydrator->setIndividuService($individuService);
        return $hydrator;
    }
}