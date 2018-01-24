<?php

namespace Application\Controller\Factory;

use Application\Controller\EcoleDoctoraleController;
use Application\Form\EcoleDoctoraleForm;
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

        $controller = new EcoleDoctoraleController();
        $controller->setEcoleDoctoraleForm($form);

        return $controller;
    }
}