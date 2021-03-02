<?php

namespace Soutenance\Service\Notifier;

use Application\Service\Acteur\ActeurService;
use Application\Service\Notification\NotificationFactory;
use Application\Service\Role\RoleService;
use Application\Service\These\TheseService;
use Application\Service\Variable\VariableService;
use Interop\Container\ContainerInterface;
use Notification\Service\NotifierServiceFactory;
use Soutenance\Service\Membre\MembreService;
use Zend\Mvc\Console\View\ViewManager as ConsoleViewManager;
use Zend\Mvc\View\Http\ViewManager as HttpViewManager;
use Zend\View\Helper\Url as UrlHelper;

class NotifierSoutenanceServiceFactory extends NotifierServiceFactory
{

    protected $notifierServiceClass = NotifierSoutenanceService::class;

    /**
     * @param ContainerInterface $container
     * @return NotifierSoutenanceService
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var NotifierSoutenanceService $service */
        $service = parent::__invoke($container);

        /**
         * @var ActeurService $acteurService
         * @var MembreService $membreService
         * @var RoleService $roleService
         * @var VariableService $variableService
         * @var TheseService $theseService
         */
        $acteurService = $container->get(ActeurService::class);
        $membreService = $container->get(MembreService::class);
        $roleService = $container->get('RoleService');
        $variableService = $container->get('VariableService');
        $theseService = $container->get('TheseService');

        /** @var HttpViewManager|ConsoleViewManager $vm */
        $vm = $container->get('ViewManager');
        /** @var UrlHelper $urlHelper */
//        $urlHelper = $vm->getHelperManager()->get('Url');
        $urlHelper = $container->get('ViewHelperManager')->get('Url');

        /** @var NotificationFactory $notificationFactory */
        $notificationFactory = $container->get(NotificationFactory::class);

        $service->setNotificationFactory($notificationFactory);
        $service->setUrlHelper($urlHelper);
        $service->setActeurService($acteurService);
        $service->setMembreService($membreService);
        $service->setRoleService($roleService);
        $service->setVariableService($variableService);
        $service->setTheseService($theseService);

        return $service;
    }
}