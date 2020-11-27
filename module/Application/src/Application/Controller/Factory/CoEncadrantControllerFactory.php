<?php

namespace Application\Controller\Factory;

use Application\Controller\CoEncadrantController;
use Application\Form\RechercherCoEncadrantForm;
use Application\Service\Acteur\ActeurService;
use Application\Service\CoEncadrant\CoEncadrantService;
use Application\Service\File\FileService;
use Application\Service\Individu\IndividuService;
use Application\Service\These\TheseService;
use Interop\Container\ContainerInterface;
use Zend\View\Renderer\PhpRenderer;

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
         * @var FileService $fileService
         * @var TheseService $theseService
         */
        $acteurService = $container->get(ActeurService::class);
        $coEncadrantService = $container->get(CoEncadrantService::class);
        $individuService = $container->get('IndividuService');
        $fileService = $container->get(FileService::class);
        $theseService = $container->get('TheseService');

        /**
         * @var RechercherCoEncadrantForm $rechercheCoEncadrantForm
         */
        $rechercheCoEncadrantForm = $container->get('FormElementManager')->get(RechercherCoEncadrantForm::class);

        /* @var $renderer PhpRenderer */
        $renderer = $container->get('ViewRenderer');

        $controller = new CoEncadrantController();
        $controller->setActeurService($acteurService);
        $controller->setCoEncadrantService($coEncadrantService);
        $controller->setIndividuService($individuService);
        $controller->setFileService($fileService);
        $controller->setTheseService($theseService);
        $controller->setRechercherCoEncadrantForm($rechercheCoEncadrantForm);
        $controller->setRenderer($renderer);
        return $controller;
    }
}