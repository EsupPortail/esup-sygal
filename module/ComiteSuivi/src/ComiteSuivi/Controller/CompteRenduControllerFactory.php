<?php

namespace ComiteSuivi\Controller;

use ComiteSuivi\Form\CompteRendu\CompteRenduForm;
use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviService;
use ComiteSuivi\Service\CompteRendu\CompteRenduService;
use ComiteSuivi\Service\Membre\MembreService;
use Zend\Mvc\Controller\ControllerManager;

class CompteRenduControllerFactory {

    /**
     * @param ControllerManager $manager
     * @return CompteRenduController
     */
    public function __invoke(ControllerManager $manager)
    {
        /**
         * @var ComiteSuiviService $comiteSuiviService
         * @var CompteRenduService $compteRenduService
         * @var MembreService $membreService
         */
        $comiteSuiviService = $manager->getServiceLocator()->get(ComiteSuiviService::class);
        $compteRenduService = $manager->getServiceLocator()->get(CompteRenduService::class);
        $membreService = $manager->getServiceLocator()->get(MembreService::class);

        /**
         * @var CompteRenduForm $compteRenduForm
         */
        $compteRenduForm = $manager->getServiceLocator()->get('FormElementManager')->get(CompteRenduForm::class);

        /** @var CompteRenduController $controller */
        $controller = new CompteRenduController();
        $controller->setComiteSuiviService($comiteSuiviService);
        $controller->setCompteRenduService($compteRenduService);
        $controller->setMembreService($membreService);
        $controller->setCompteRenduForm($compteRenduForm);
        return $controller;
    }


}