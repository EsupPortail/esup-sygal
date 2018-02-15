<?php

namespace Application\Controller\Factory;

use Application\Controller\EtablissementController;
use Application\Form\EtablissementForm;
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

        $controller = new EtablissementController();
        $controller->setEtablissementForm($form);


        return $controller;
    }
}