<?php

namespace Application\Service\Notification;

use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Individu\IndividuService;
use Application\Service\Role\RoleService;
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
         * @var VariableService         $variableService
         * @var EcoleDoctoraleService   $ecoleDoctoraleService
         * @var UniteRechercheService   $uniteRechercheService
         * @var IndividuService         $individuService
         * @var RoleService             $roleService
         */

        $variableService = $serviceLocator->get('VariableService');
        $ecoleDoctoraleService = $serviceLocator->get('EcoleDoctoraleService');
        $uniteRechercheService = $serviceLocator->get('UniteRechercheService');
        $individuService = $serviceLocator->get('IndividuService');
        $roleService = $serviceLocator->get('RoleService');

        $service->setVariableService($variableService);
        $service->setEcoleDoctoraleService($ecoleDoctoraleService);
        $service->setUniteRechercheService($uniteRechercheService);
        $service->setIndividuService($individuService);
        $service->setRoleService($roleService);

        return $service;
    }
}
