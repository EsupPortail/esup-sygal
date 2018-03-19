<?php

namespace Application\Controller\Factory;

use Application\Controller\UniteRechercheController;
use Application\Form\UniteRechercheForm;
use Application\Service\Individu\IndividuService;
use Application\Service\Individu\IndividuServiceAwareInterface;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Role\RoleService;
use Application\Service\UniteRecherche\UniteRechercheService;
use UnicaenLdap\Service\People as LdapPeopleService;
use Zend\Mvc\Controller\ControllerManager;

class UniteRechercheControllerFactory implements IndividuServiceAwareInterface
{
    use IndividuServiceAwareTrait;

    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return UniteRechercheController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        /** @var UniteRechercheForm $form */
        $form = $controllerManager->getServiceLocator()->get('FormElementManager')->get('UniteRechercheForm');

        /**
         * @var UniteRechercheService $uniteRechercheService
         * @var LdapPeopleService $ldapPeopleService
         * @var IndividuService $individuService
         * @var RoleService $roleService
         */
        $uniteRechercheService = $controllerManager->getServiceLocator()->get('UniteRechercheService');
        $ldapPeopleService  = $controllerManager->getServiceLocator()->get('LdapServicePeople');
        $individuService = $controllerManager->getServiceLocator()->get('IndividuService');
        $roleService = $controllerManager->getServiceLocator()->get('RoleService');

        $controller = new UniteRechercheController();
        $controller->setUniteRechercheService($uniteRechercheService);
        $controller->setLdapPeopleService($ldapPeopleService);
        $controller->setIndividuService($individuService);
        $controller->setRoleService($roleService);
        $controller->setUniteRechercheForm($form);

        return $controller;
    }
}