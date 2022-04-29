<?php

namespace Soutenance\Service\Notifier;

use Application\Service\Acteur\ActeurService;
use Application\Service\Email\EmailTheseService;
use Application\Service\Notification\NotificationFactory;
use Application\Service\Role\RoleService;
use Application\Service\These\TheseService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Variable\VariableService;
use Interop\Container\ContainerInterface;
use Laminas\Mvc\Console\View\ViewManager as ConsoleViewManager;
use Laminas\Mvc\View\Http\ViewManager as HttpViewManager;
use Laminas\View\Helper\Url as UrlHelper;
use Notification\Service\NotifierServiceFactory;
use Soutenance\Service\Membre\MembreService;

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
         * @var UtilisateurService $utilisateurService
         */
        $acteurService = $container->get(ActeurService::class);
        $membreService = $container->get(MembreService::class);
        $roleService = $container->get('RoleService');
        $theseService = $container->get('TheseService');
        $emailTheseService = $container->get(EmailTheseService::class);

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
        $service->setTheseService($theseService);
        $service->setEmailTheseService($emailTheseService);

        return $service;
    }
}