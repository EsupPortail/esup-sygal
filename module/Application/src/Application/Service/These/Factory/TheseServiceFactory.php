<?php

namespace Application\Service\These\Factory;

use Application\Service\Fichier\FichierService;
use Application\Service\Notification\NotificationService;
use Application\Service\These\TheseService;
use Application\Service\Validation\ValidationService;
use Application\Service\Variable\VariableService;
use Zend\ServiceManager\ServiceManager;

class TheseServiceFactory
{
    /**
     * Create service
     *
     * @param ServiceManager $serviveManager
     * @return TheseService
     */
    public function __invoke(ServiceManager $serviveManager)
    {
        /**
         * @var ValidationService $validationService
         * @var NotificationService $notificationService
         * @var FichierService $fichierService
         * @var VariableService $variableService
         */
        $validationService = $serviveManager->get('ValidationService');
        $notificationService = $serviveManager->get(NotificationService::class);
        $fichierService = $serviveManager->get('FichierService');
        $variableService = $serviveManager->get('VariableService');


        $service = new TheseService();
        $service->setValidationService($validationService);
        $service->setNotificationService($notificationService);
        $service->setFichierService($fichierService);
        $service->setVariableService($variableService);

        return $service;
    }
}
