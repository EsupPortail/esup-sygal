<?php

namespace Indicateur\Service\Factory;

use Application\Service\FichierThese\FichierTheseService;
use Application\Service\Notification\NotifierService;
use Application\Service\These\TheseService;
use Application\Service\UserContextService;
use Application\Service\Validation\ValidationService;
use Application\Service\Variable\VariableService;
use Doctrine\ORM\EntityManager;
use Indicateur\Service\IndicateurService;
use Zend\ServiceManager\ServiceLocatorInterface;

class IndicateurServiceFactory
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviveManager
     * @return IndicateurService
     */
    public function __invoke(ServiceLocatorInterface $serviveManager)
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $serviveManager->get('doctrine.entitymanager.orm_default');

        $service = new IndicateurService();
        $service->setEntityManager($entityManager);

        return $service;
    }
}
