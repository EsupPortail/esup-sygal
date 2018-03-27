<?php

namespace Application\Controller\Factory;

use Application\Controller\DoctorantController;
use Application\Service\Doctorant\DoctorantService;
use Application\Service\Variable\VariableService;
use Zend\Mvc\Controller\ControllerManager;
use Application\Service\MailConfirmationService;

class DoctorantControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return DoctorantController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        /**
         * @var VariableService $variableService
         * @var DoctorantService $doctorantService
         * @var MailConfirmationService $mailConfirmationService
         */
        $variableService = $controllerManager->getServiceLocator()->get('VariableService');
        $doctorantService = $controllerManager->getServiceLocator()->get('DoctorantService');
        $mailConfirmationService = $controllerManager->getServiceLocator()->get('MailConfirmationService');

        $controller = new DoctorantController();
        $controller->setVariableService($variableService);
        $controller->setDoctorantService($doctorantService);
        $controller->setMailConfirmationService($mailConfirmationService);

        return $controller;
    }
}
