<?php

namespace Application\Controller\Factory;

use Application\Controller\EcoleDoctoraleController;
use Application\Form\EcoleDoctoraleForm;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Individu\IndividuService;
use Application\Service\Role\RoleService;
use UnicaenLdap\Service\People as LdapPeopleService;
use Zend\Mvc\Controller\ControllerManager;

class EcoleDoctoraleControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return EcoleDoctoraleController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        /** @var EcoleDoctoraleForm $form */
        $form = $controllerManager->getServiceLocator()->get('FormElementManager')->get('EcoleDoctoraleForm');

        /**
         * @var EcoleDoctoraleService $ecoleDoctoralService
         * @var LdapPeopleService $ldapPeopleService
         * @var IndividuService $individuService
         * @var RoleService $roleService
         */
        $ecoleDoctoralService = $controllerManager->getServiceLocator()->get('EcoleDoctoraleService');
        $ldapPeopleService  = $controllerManager->getServiceLocator()->get('LdapServicePeople');
        $individuService = $controllerManager->getServiceLocator()->get('IndividuService');
        $roleService = $controllerManager->getServiceLocator()->get('RoleService');

        $controller = new EcoleDoctoraleController();
        $controller->setEcoleDoctoraleService($ecoleDoctoralService);
        $controller->setLdapPeopleService($ldapPeopleService);
        $controller->setIndividuService($individuService);
        $controller->setRoleService($roleService);
        $controller->setEcoleDoctoraleForm($form);

        return $controller;
    }
}