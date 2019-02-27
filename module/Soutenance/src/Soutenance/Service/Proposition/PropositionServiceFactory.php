<?php

namespace  Soutenance\Service\Proposition;

use Application\Service\Etablissement\EtablissementService;
use Application\Service\File\FileService;
use Application\Service\Notification\NotifierService;
use Application\Service\Validation\ValidationService;
use Application\Service\Variable\VariableService;
use Doctrine\ORM\EntityManager;
use Soutenance\Service\Notifier\NotifierSoutenanceService;
use Soutenance\Service\Parametre\ParametreService;
use Zend\ServiceManager\ServiceLocatorInterface;

class PropositionServiceFactory
{
    public function __invoke(ServiceLocatorInterface $servicelocator)
    {
        /**
         * @var EntityManager $entityManager
         * @var ValidationService $validationService
         * @var NotifierService $notifierService
         * @var NotifierSoutenanceService $notifierSoutenanceService
         * @var ParametreService $parametreService
         * @var VariableService $variableService
         * @var FileService $fileService
         * @var EtablissementService $etablissamentService
         */
        $entityManager = $servicelocator->get('doctrine.entitymanager.orm_default');
        $validationService = $servicelocator->get('ValidationService');
        $notifierService = $servicelocator->get(NotifierService::class);
        $notifierSoutenanceService = $servicelocator->get(NotifierSoutenanceService::class);
        $parametreService = $servicelocator->get(ParametreService::class);
        $variableService = $servicelocator->get('VariableService');
        $fileService = $servicelocator->get(FileService::class);
        $etablissamentService = $servicelocator->get(EtablissementService::class);

        /** @var PropositionService $service */
        $service = new PropositionService();
        $service->setEntityManager($entityManager);
        $service->setValidationService($validationService);
        $service->setNotifierService($notifierService);
        $service->setNotifierSoutenanceService($notifierSoutenanceService);
        $service->setParametreService($parametreService);
        $service->setVariableService($variableService);
        $service->setFileService($fileService);
        $service->setEtablissementService($etablissamentService);

        return $service;
    }
}
