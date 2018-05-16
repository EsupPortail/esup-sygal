<?php

namespace Application\Service\These\Factory;

use Application\Service\Fichier\FichierService;
use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Application\Service\Validation\ValidationService;
use Application\Service\Variable\VariableService;
use Zend\ServiceManager\ServiceLocatorInterface;

class TheseServiceFactory
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviveManager
     * @return TheseService
     */
    public function __invoke(ServiceLocatorInterface $serviveManager)
    {
        /**
         * @var ValidationService $validationService
         * @var NotifierService $notifierService
         * @var FichierService $fichierService
         * @var VariableService $variableService
         */
        $validationService = $serviveManager->get('ValidationService');
        $notifierService = $serviveManager->get(NotifierService::class);
        $fichierService = $serviveManager->get('FichierService');
        $variableService = $serviveManager->get('VariableService');

        $service = new TheseService();
        $service->setValidationService($validationService);
        $service->setNotifierService($notifierService);
        $service->setFichierService($fichierService);
        $service->setVariableService($variableService);

        return $service;
    }
}
