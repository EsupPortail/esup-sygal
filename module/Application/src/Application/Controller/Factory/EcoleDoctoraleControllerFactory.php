<?php

namespace Application\Controller\Factory;

use Application\Controller\EcoleDoctoraleController;
use Application\Form\EcoleDoctoraleForm;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Etablissement\EtablissementServiceLocateTrait;
use Application\Service\Individu\IndividuService;
use Application\Service\Role\RoleService;
use UnicaenLdap\Service\People as LdapPeopleService;
use Zend\Mvc\Controller\ControllerManager;

class EcoleDoctoraleControllerFactory
{
    use EtablissementServiceLocateTrait;

    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return EcoleDoctoraleController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        /** @var EcoleDoctoraleForm $form */
        $form = $sl->get('FormElementManager')->get('EcoleDoctoraleForm');

        /**
         * @var EcoleDoctoraleService $ecoleDoctoralService
         * @var LdapPeopleService $ldapPeopleService
         * @var IndividuService $individuService
         * @var RoleService $roleService
         */
        $ecoleDoctoralService = $sl->get('EcoleDoctoraleService');
        $ldapPeopleService  = $sl->get('LdapServicePeople');
        $individuService = $sl->get('IndividuService');
        $roleService = $sl->get('RoleService');

        $controller = new EcoleDoctoraleController();
        $controller->setEcoleDoctoraleService($ecoleDoctoralService);
        $controller->setLdapPeopleService($ldapPeopleService);
        $controller->setIndividuService($individuService);
        $controller->setRoleService($roleService);
        $controller->setEtablissementService($this->locateEtablissementService($sl));
        $controller->setEcoleDoctoraleForm($form);

        return $controller;
    }
}