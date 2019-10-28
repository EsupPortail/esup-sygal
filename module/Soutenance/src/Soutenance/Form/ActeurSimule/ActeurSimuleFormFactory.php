<?php

namespace Soutenance\Form\ActeurSimule;

use Application\Service\Etablissement\EtablissementService;
use Application\Service\Individu\IndividuService;
use Application\Service\Role\RoleService;
use Soutenance\Service\IndividuSimulable\IndividuSimulableService;
use Zend\Form\FormElementManager;

class ActeurSimuleFormFactory {

    public function __invoke(FormElementManager $manager) {

        /**
         * @var RoleService $roleService
         * @var EtablissementService $etablissementService
         * @var IndividuService $individuService
         * @var IndividuSimulableService $simulableService
         * @var ActeurSimuleHydrator $hydrator
         */
        $roleService = $manager->getServiceLocator()->get('RoleService');
        $etablissementService = $manager->getServiceLocator()->get(EtablissementService::class);
        $individuService = $manager->getServiceLocator()->get('IndividuService');
        $simulableService = $manager->getServiceLocator()->get(IndividuSimulableService::class);
        $hydrator = $manager->getServiceLocator()->get('HydratorManager')->get(ActeurSimuleHydrator::class);

        /** @var ActeurSimuleForm $form */
        $form = new ActeurSimuleForm();
        $form->setRoleService($roleService);
        $form->setEtablissementService($etablissementService);
        $form->setIndividuService($individuService);
        $form->setIndividuSimulableService($simulableService);
        $form->setHydrator($hydrator);
        return $form;
    }
}