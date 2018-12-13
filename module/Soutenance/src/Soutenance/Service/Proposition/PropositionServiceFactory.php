<?php

namespace  Soutenance\Service\Proposition;

use Application\Service\Notification\NotifierService;
use Application\Service\Validation\ValidationService;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class PropositionServiceFactory
{
    public function __invoke(ServiceLocatorInterface $servicelocator)
    {
        /**
         * @var EntityManager $entityManager
         * @var ValidationService $validationService
         * @var NotifierService $notifierService
         */
        $entityManager = $servicelocator->get('doctrine.entitymanager.orm_default');
        $validationService = $servicelocator->get('ValidationService');
        $notifierService = $servicelocator->get(NotifierService::class);

        /** @var PropositionService $service */
        $service = new PropositionService();
        $service->setEntityManager($entityManager);
        $service->setValidationService($validationService);
        $service->setNotifierService($notifierService);

        return $service;
    }
}
