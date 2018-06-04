<?php

namespace Application\Controller\Factory;

use Application\Controller\UniteRechercheController;
use Application\Form\UniteRechercheForm;
use Application\Service\Etablissement\EtablissementServiceLocateTrait;
use Application\Service\Individu\IndividuService;
use Application\Service\Notification\NotificationService;
use Application\Service\Role\RoleService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Zend\Mvc\Controller\ControllerManager;

class UniteRechercheControllerFactory
{
    use EtablissementServiceLocateTrait;

    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return UniteRechercheController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        /** @var UniteRechercheForm $form */
        $form = $sl->get('FormElementManager')->get('UniteRechercheForm');

        /**
         * @var UniteRechercheService $uniteRechercheService
         * @var IndividuService $individuService
         * @var RoleService $roleService
         * @var NotificationService $notificationService
         */
        $uniteRechercheService = $sl->get('UniteRechercheService');
        $individuService = $sl->get('IndividuService');
        $roleService = $sl->get('RoleService');
        $notificationService = $controllerManager->getServiceLocator()->get(NotificationService::class);

        $controller = new UniteRechercheController();
        $controller->setUniteRechercheService($uniteRechercheService);
        $controller->setIndividuService($individuService);
        $controller->setRoleService($roleService);
        $controller->setEtablissementService($this->locateEtablissementService($sl));
        $controller->setUniteRechercheForm($form);
        $controller->setNotificationService($notificationService);

        return $controller;
    }
}