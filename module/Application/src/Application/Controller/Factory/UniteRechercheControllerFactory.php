<?php

namespace Application\Controller\Factory;

use Application\Controller\UniteRechercheController;
use Application\Form\UniteRechercheForm;
use Zend\Mvc\Controller\ControllerManager;

class UniteRechercheControllerFactory
{
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

        $controller = new UniteRechercheController();
        $controller->setUniteRechercheForm($form);

        return $controller;
    }
}