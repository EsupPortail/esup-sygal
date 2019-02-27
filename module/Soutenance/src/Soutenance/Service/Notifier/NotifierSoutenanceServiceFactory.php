<?php

namespace Soutenance\Service\Notifier;

use Application\Service\Notification\NotifierServiceFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class NotifierSoutenanceServiceFactory extends NotifierServiceFactory {

    protected $notifierServiceClass = NotifierSoutenanceService::class;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return NotifierSoutenanceService
     */
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /** @var NotifierSoutenanceService $service */
        $service = parent::__invoke($serviceLocator);
        return $service;
    }
}