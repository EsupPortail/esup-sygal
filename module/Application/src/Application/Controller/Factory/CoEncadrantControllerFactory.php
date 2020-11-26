<?php

namespace Application\Controller\Factory;

use Application\Controller\CoEncadrantController;
use Application\Form\RechercherCoEncadrantForm;
use Application\Service\Acteur\ActeurService;
use Application\Service\CoEncadrant\CoEncadrantService;
use Application\Service\Individu\IndividuService;
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
         * @var ActeurService $acteurService
         * @var CoEncadrantService $coEncadrantService
         * @var IndividuService $individuService
         * @var TheseService $theseService
         */
        $acteurService = $container->get(ActeurService::class);
        $coEncadrantService = $container->get(CoEncadrantService::class);
        $individuService = $container->get('IndividuService');
        $theseService = $container->get('TheseService');

        /**
         * @var RechercherCoEncadrantForm $rechercheCoEncadrantForm
         */
        $rechercheCoEncadrantForm = $container->get('FormElementManager')->get(RechercherCoEncadrantForm::class);

        $controller = new CoEncadrantController();
        $controller->setActeurService($acteurService);
        $controller->setCoEncadrantService($coEncadrantService);
        $controller->setIndividuService($individuService);
        $controller->setTheseService($theseService);
        $controller->setRechercherCoEncadrantForm($rechercheCoEncadrantForm);
        return $controller;
    }
}