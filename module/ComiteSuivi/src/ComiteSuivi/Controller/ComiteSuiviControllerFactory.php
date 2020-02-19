<?php

namespace ComiteSuivi\Controller;

use Application\Service\These\TheseService;
use ComiteSuivi\Form\ComiteSuivi\ComiteSuiviForm;
use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviService;
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
         * @var TheseService $theseService
         */
        $comiteSuiviService = $manager->getServiceLocator()->get(ComiteSuiviService::class);
        $theseService = $manager->getServiceLocator()->get('TheseService');

        /**
         * @var ComiteSuiviForm $comiteSuiviForm
         */
        $comiteSuiviForm = $manager->getServiceLocator()->get('FormElementManager')->get(ComiteSuiviForm::class);

        /** @var ComiteSuiviController $controller */
        $controller = new ComiteSuiviController();
        $controller->setComiteSuiviService($comiteSuiviService);
        $controller->setTheseService($theseService);
        $controller->setComiteSuiviForm($comiteSuiviForm);
        return $controller;
    }


}