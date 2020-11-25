<?php

namespace Application\Controller\Factory;

use Application\Controller\CoEncadrantController;
use Application\Form\RechercherCoEncadrantForm;
use Interop\Container\ContainerInterface;

class CoEncadrantControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return CoEncadrantController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var RechercherCoEncadrantForm $rechercheCoEncadrantForm
         */
        $rechercheCoEncadrantForm = $container->get('FormElementManager')->get(RechercherCoEncadrantForm::class);

        $controller = new CoEncadrantController();
        $controller->setRechercherCoEncadrantForm($rechercheCoEncadrantForm);
        return $controller;
    }
}