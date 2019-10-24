<?php

namespace Soutenance\Controller;

use Application\Service\Source\SourceService;
use Application\Service\These\TheseService;
use Soutenance\Form\ActeurSimule\ActeurSimuleForm;
use Soutenance\Service\Simulation\SimulationService;
use Zend\Mvc\Controller\ControllerManager;

class SimulationControllerFactory {

    public function __invoke(ControllerManager $manager)
    {
        /**
         * @var SimulationService $simulationService
         * @var TheseService $theseService
         * @var SourceService $sourceService
         * @var ActeurSimuleForm $acteurSimuleForm
         */
        $simulationService = $manager->getServiceLocator()->get(SimulationService::class);
        $theseService = $manager->getServiceLocator()->get('TheseService');
        $sourceService = $manager->getServiceLocator()->get(SourceService::class);
        $acteurSimuleForm = $manager->getServiceLocator()->get('FormElementManager')->get(ActeurSimuleForm::class);

        /** @var SimulationController $controller */
        $controller = new SimulationController();
        $controller->setSimulationService($simulationService);
        $controller->setTheseService($theseService);
        $controller->setSourceService($sourceService);
        $controller->setActeurSimuleForm($acteurSimuleForm);
        return $controller;
    }
}