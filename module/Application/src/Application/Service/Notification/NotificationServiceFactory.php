<?php

namespace Application\Service\Notification;

use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\Service\Variable\VariableService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @author Unicaen
 */
class NotificationServiceFactory extends \Notification\Service\NotificationServiceFactory
{
    protected $notificationServiceClass = NotificationService::class;

    /**
     * Create service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return NotificationService
     */
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /** @var NotificationService $service */
        $service = parent::__invoke($serviceLocator);

        /**
         * @var VariableService       $variableService
         * @var EcoleDoctoraleService $ecoleDoctoraleService
         * @var UniteRechercheService $uniteRechercheService
         */
        $variableService = $serviceLocator->get('VariableService');
        $ecoleDoctoraleService = $serviceLocator->get('EcoleDoctoraleService');
        $uniteRechercheService = $serviceLocator->get('UniteRechercheService');

        $service->setVariableService($variableService);
        $service->setEcoleDoctoraleService($ecoleDoctoraleService);
        $service->setUniteRechercheService($uniteRechercheService);

        return $service;
    }
}
