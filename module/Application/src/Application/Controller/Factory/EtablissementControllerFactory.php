<?php

namespace Application\Controller\Factory;

use Application\Controller\EtablissementController;
use Application\Form\EtablissementForm;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Individu\IndividuService;
use Application\Service\Notification\NotificationService;
use Application\Service\Role\RoleService;
use UnicaenLdap\Service\People as LdapPeopleService;
use Zend\Mvc\Controller\ControllerManager;

class EtablissementControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return EtablissementController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        /** @var EtablissementForm $form */
        $form = $controllerManager->getServiceLocator()->get('FormElementManager')->get('EtablissementForm');

        /**
         * @var EtablissementService $etablissmentService
         * @var LdapPeopleService $ldapPeopleService
         * @var IndividuService $individuService
         * @var RoleService $roleService
         * @var NotificationService $notificationService
         */
        $etablissmentService = $controllerManager->getServiceLocator()->get('EtablissementService');
        $ldapPeopleService  = $controllerManager->getServiceLocator()->get('LdapServicePeople');
        $individuService = $controllerManager->getServiceLocator()->get('IndividuService');
        $roleService = $controllerManager->getServiceLocator()->get('RoleService');
        $notificationService = $controllerManager->getServiceLocator()->get(NotificationService::class);

        $controller = new EtablissementController();
        $controller->setEtablissementService($etablissmentService);
        $controller->setLdapPeopleService($ldapPeopleService);
        $controller->setIndividuService($individuService);
        $controller->setRoleService($roleService);
        $controller->setEtablissementForm($form);
        $controller->setNotificationService($notificationService);


        return $controller;
    }
}