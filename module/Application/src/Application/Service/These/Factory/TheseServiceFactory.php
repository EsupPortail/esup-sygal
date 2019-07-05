<?php

namespace Application\Service\These\Factory;

use Application\Service\Etablissement\EtablissementService;
use Application\Service\FichierThese\FichierTheseService;
use Application\Service\File\FileService;
use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Application\Service\UserContextService;
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
         * @var ValidationService   $validationService
         * @var NotifierService     $notifierService
         * @var FichierTheseService $fichierTheseService
         * @var VariableService     $variableService
         * @var UserContextService  $userContextService
         */
        $validationService = $serviveManager->get('ValidationService');
        $notifierService = $serviveManager->get(NotifierService::class);
        $fichierTheseService = $serviveManager->get('FichierTheseService');
        $variableService = $serviveManager->get('VariableService');
        $userContextService = $serviveManager->get('UserContextService');

        /** @var EtablissementService $etablissementService */
        $etablissementService = $serviveManager->get(EtablissementService::class);

        /** @var FileService $fileService */
        $fileService = $serviveManager->get(FileService::class);

        $service = new TheseService();
        $service->setValidationService($validationService);
        $service->setNotifierService($notifierService);
        $service->setFichierTheseService($fichierTheseService);
        $service->setVariableService($variableService);
        $service->setUserContextService($userContextService);
        $service->setEtablissementService($etablissementService);
        $service->setFileService($fileService);

        return $service;
    }
}
