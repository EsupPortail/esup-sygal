<?php

namespace Soutenance\Form\ActeurSimule;

use Application\Service\Etablissement\EtablissementService;
use Application\Service\Role\RoleService;
use Zend\Form\FormElementManager;

class ActeurSimuleFormFactory {

    public function __invoke(FormElementManager $manager) {

        /**
         * @var RoleService $roleService
         * @var EtablissementService $etablissementService
         * @var ActeurSimuleHydrator $hydrator
         */
        $roleService = $manager->getServiceLocator()->get('RoleService');
        $etablissementService = $manager->getServiceLocator()->get(EtablissementService::class);
        $hydrator = $manager->getServiceLocator()->get('HydratorManager')->get(ActeurSimuleHydrator::class);

        /** @var ActeurSimuleForm $form */
        $form = new ActeurSimuleForm();
        $form->setRoleService($roleService);
        $form->setEtablissementService($etablissementService);
        $form->setHydrator($hydrator);
        return $form;
    }
}