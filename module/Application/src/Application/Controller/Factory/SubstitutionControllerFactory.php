<?php

namespace Application\Controller\Factory;

use Application\Controller\SubstitutionController;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Etablissement\EtablissementServiceAwareTrait;
use Zend\Mvc\Controller\ControllerManager;

class SubstitutionControllerFactory
{
    use EtablissementServiceAwareTrait;

    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return SubstitutionController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        /**
         * @var EtablissementService $etablissementService
         */
        $etablissementService = $sl->get('EtablissementService');

        $controller = new SubstitutionController();
        $controller->setEtablissementService($etablissementService);

        return $controller;
    }
}