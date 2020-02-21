<?php

namespace ComiteSuivi\Controller;

use Application\Service\Individu\IndividuService;
use Application\Service\These\TheseService;
use ComiteSuivi\Form\ComiteSuivi\ComiteSuiviForm;
use ComiteSuivi\Form\CompteRendu\CompteRenduForm;
use ComiteSuivi\Form\Membre\MembreForm;
use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviService;
use ComiteSuivi\Service\CompteRendu\CompteRenduService;
use ComiteSuivi\Service\Membre\MembreService;
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
         * @var TheseService $theseService
         */
        $comiteSuiviService = $manager->getServiceLocator()->get(ComiteSuiviService::class);
        $compteRenduService = $manager->getServiceLocator()->get(CompteRenduService::class);
        $individuService = $manager->getServiceLocator()->get('IndividuService');
        $membreService = $manager->getServiceLocator()->get(MembreService::class);
        $theseService = $manager->getServiceLocator()->get('TheseService');

        /**
         * @var ComiteSuiviForm $comiteSuiviForm
         * @var CompteRenduForm $compteRenduForm
         * @var MembreForm $membreForm
         */
        $comiteSuiviForm = $manager->getServiceLocator()->get('FormElementManager')->get(ComiteSuiviForm::class);
        $compteRenduForm = $manager->getServiceLocator()->get('FormElementManager')->get(CompteRenduForm::class);
        $membreForm = $manager->getServiceLocator()->get('FormElementManager')->get(MembreForm::class);

        /** @var ComiteSuiviController $controller */
        $controller = new ComiteSuiviController();
        $controller->setComiteSuiviService($comiteSuiviService);
        $controller->setCompteRenduService($compteRenduService);
        $controller->setIndividuService($individuService);
        $controller->setMembreService($membreService);
        $controller->setTheseService($theseService);
        $controller->setComiteSuiviForm($comiteSuiviForm);
        $controller->setCompteRenduForm($compteRenduForm);
        $controller->setMembreForm($membreForm);
        return $controller;
    }


}