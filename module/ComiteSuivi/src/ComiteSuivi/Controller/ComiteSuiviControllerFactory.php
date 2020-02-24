<?php

namespace ComiteSuivi\Controller;

use Application\Service\Individu\IndividuService;
use Application\Service\Role\RoleService;
use Application\Service\These\TheseService;
use Application\Service\Validation\ValidationService;
use ComiteSuivi\Form\ComiteSuivi\ComiteSuiviForm;
use ComiteSuivi\Form\ComiteSuivi\RefusForm;
use ComiteSuivi\Form\CompteRendu\CompteRenduForm;
use ComiteSuivi\Form\Membre\MembreForm;
use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviService;
use ComiteSuivi\Service\CompteRendu\CompteRenduService;
use ComiteSuivi\Service\Membre\MembreService;
use ComiteSuivi\Service\Notifier\NotifierService;
use Zend\Mvc\Controller\ControllerManager;

class ComiteSuiviControllerFactory {

    /**
     * @param ControllerManager $manager
     * @return ComiteSuiviController
     */
    public function __invoke(ControllerManager $manager)
    {
        /**
         * @var ComiteSuiviService $comiteSuiviService
         * @var CompteRenduService $compteRenduService
         * @var IndividuService $individuService
         * @var MembreService $membreService
         * @var NotifierService $notifierService
         * @var RoleService $roleService
         * @var TheseService $theseService
         * @var ValidationService $validationService
         */
        $comiteSuiviService = $manager->getServiceLocator()->get(ComiteSuiviService::class);
        $compteRenduService = $manager->getServiceLocator()->get(CompteRenduService::class);
        $individuService = $manager->getServiceLocator()->get('IndividuService');
        $membreService = $manager->getServiceLocator()->get(MembreService::class);
        $notifierService = $manager->getServiceLocator()->get(NotifierService::class);
        $roleService = $manager->getServiceLocator()->get('RoleService');
        $theseService = $manager->getServiceLocator()->get('TheseService');
        $validationService = $manager->getServiceLocator()->get('ValidationService');

        /**
         * @var ComiteSuiviForm $comiteSuiviForm
         * @var CompteRenduForm $compteRenduForm
         * @var MembreForm $membreForm
         * @var RefusForm $refusForm
         */
        $comiteSuiviForm = $manager->getServiceLocator()->get('FormElementManager')->get(ComiteSuiviForm::class);
        $compteRenduForm = $manager->getServiceLocator()->get('FormElementManager')->get(CompteRenduForm::class);
        $membreForm = $manager->getServiceLocator()->get('FormElementManager')->get(MembreForm::class);
        $refusForm = $manager->getServiceLocator()->get('FormElementManager')->get(RefusForm::class);

        /** @var ComiteSuiviController $controller */
        $controller = new ComiteSuiviController();

        $controller->setComiteSuiviService($comiteSuiviService);
        $controller->setCompteRenduService($compteRenduService);
        $controller->setIndividuService($individuService);
        $controller->setMembreService($membreService);
        $controller->setNotifierService($notifierService);
        $controller->setRoleService($roleService);
        $controller->setTheseService($theseService);
        $controller->setValidationService($validationService);

        $controller->setComiteSuiviForm($comiteSuiviForm);
        $controller->setCompteRenduForm($compteRenduForm);
        $controller->setMembreForm($membreForm);
        $controller->setRefusForm($refusForm);

        return $controller;
    }


}