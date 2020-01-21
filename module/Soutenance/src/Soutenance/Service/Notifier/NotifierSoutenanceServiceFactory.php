<?php

namespace Soutenance\Service\Notifier;

use Application\Service\Acteur\ActeurService;
use Application\Service\Role\RoleService;
use Application\Service\Variable\VariableService;
use Application\Service\Notification\NotificationFactory;
use Notification\Service\NotifierServiceFactory;
use Soutenance\Service\Membre\MembreService;
use Zend\Mvc\View\Console\ViewManager as ConsoleViewManager;
use Zend\Mvc\View\Http\ViewManager as HttpViewManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\Url as UrlHelper;

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

        /**
         * @var ActeurService           $acteurService
         * @var MembreService           $membreService
         * @var RoleService             $roleService
         * @var VariableService         $variableService
         */
        $acteurService = $serviceLocator->get(ActeurService::class);
        $membreService = $serviceLocator->get(MembreService::class);
        $roleService = $serviceLocator->get('RoleService');
        $variableService = $serviceLocator->get('VariableService');

        /** @var HttpViewManager|ConsoleViewManager $vm */
        $vm = $serviceLocator->get('ViewManager');
        /** @var UrlHelper $urlHelper */
        $urlHelper = $vm->getHelperManager()->get('Url');

        /** @var NotificationFactory $notificationFactory */
        $notificationFactory = $serviceLocator->get(NotificationFactory::class);

        $service->setNotificationFactory($notificationFactory);
        $service->setUrlHelper($urlHelper);
        $service->setActeurService($acteurService);
        $service->setMembreService($membreService);
        $service->setRoleService($roleService);
        $service->setVariableService($variableService);

        return $service;
    }
}