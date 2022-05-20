<?php

namespace Application\Service\Notification;

use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Individu\Service\IndividuService;
use Application\Service\Role\RoleService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\Service\Variable\VariableService;
use Interop\Container\ContainerInterface;
use Laminas\Mvc\Console\View\ViewManager as ConsoleViewManager;
use Laminas\Mvc\View\Http\ViewManager as HttpViewManager;
use Laminas\View\Helper\Url as UrlHelper;

/**
 * @author Unicaen
 */
class NotifierServiceFactory extends \Notification\Service\NotifierServiceFactory
{
    protected $notifierServiceClass = NotifierService::class;

    /**
     * Create service.
     *
     * @param ContainerInterface $container
     * @return NotifierService
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var NotifierService $service */
        $service = parent::__invoke($container);

        /**
         * @var VariableService         $variableService
         * @var EcoleDoctoraleService   $ecoleDoctoraleService
         * @var UniteRechercheService   $uniteRechercheService
         * @var IndividuService         $individuService
         * @var RoleService             $roleService
         */
        $variableService = $container->get('VariableService');
        $ecoleDoctoraleService = $container->get('EcoleDoctoraleService');
        $uniteRechercheService = $container->get('UniteRechercheService');
        $individuService = $container->get(IndividuService::class);
        $roleService = $container->get('RoleService');

        /** @var HttpViewManager|ConsoleViewManager $vm */
        $vm = $container->get('ViewManager');
        /** @var UrlHelper $urlHelper */
//        $urlHelper = $vm->getHelperManager()->get('Url');
        $urlHelper = $container->get('ViewHelperManager')->get('Url');

        /** @var NotificationFactory $notificationFactory */
        $notificationFactory = $container->get(NotificationFactory::class);

        $service->setNotificationFactory($notificationFactory);
        $service->setVariableService($variableService);
        $service->setEcoleDoctoraleService($ecoleDoctoraleService);
        $service->setUniteRechercheService($uniteRechercheService);
        $service->setUrlHelper($urlHelper);
        $service->setIndividuService($individuService);
        $service->setRoleService($roleService);

        return $service;
    }
}
