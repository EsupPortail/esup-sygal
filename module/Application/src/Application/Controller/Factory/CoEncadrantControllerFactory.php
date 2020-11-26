<?php

namespace Application\Controller\Factory;

use Application\Controller\CoEncadrantController;
use Application\Form\RechercherCoEncadrantForm;
use Application\Service\CoEncadrant\CoEncadrantService;
use Application\Service\These\TheseService;
use Interop\Container\ContainerInterface;

class CoEncadrantControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return CoEncadrantController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var CoEncadrantService $coEncadrantService
         * @var TheseService $theseService
         */
        $coEncadrantService = $container->get(CoEncadrantService::class);
        $theseService = $container->get('TheseService');

        /**
         * @var RechercherCoEncadrantForm $rechercheCoEncadrantForm
         */
        $rechercheCoEncadrantForm = $container->get('FormElementManager')->get(RechercherCoEncadrantForm::class);

        $controller = new CoEncadrantController();
        $controller->setCoEncadrantService($coEncadrantService);
        $controller->setTheseService($theseService);
        $controller->setRechercherCoEncadrantForm($rechercheCoEncadrantForm);
        return $controller;
    }
}