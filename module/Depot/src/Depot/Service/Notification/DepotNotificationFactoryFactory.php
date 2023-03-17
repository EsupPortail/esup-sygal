<?php

namespace Depot\Service\Notification;

use Application\Service\Email\EmailTheseService;
use Application\Service\Role\RoleService;
use Application\Service\Variable\VariableService;
use Interop\Container\ContainerInterface;
use Laminas\View\Helper\Url as UrlHelper;
use Notification\Factory\NotificationFactoryFactory;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\UniteRecherche\UniteRechercheService;
use UnicaenApp\Options\ModuleOptions;

/**
 * @author Unicaen
 */
class DepotNotificationFactoryFactory extends NotificationFactoryFactory
{
    /**
     * @var string
     */
    protected string $class = DepotNotificationFactory::class;

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DepotNotificationFactory
    {
        /** @var DepotNotificationFactory $factory */
        $factory = parent::__invoke($container);

        /**
         * @var VariableService       $variableService
         * @var EcoleDoctoraleService $ecoleDoctoraleService
         * @var UniteRechercheService $uniteRechercheService
         */
        $variableService = $container->get('VariableService');
        $ecoleDoctoraleService = $container->get('EcoleDoctoraleService');
        $uniteRechercheService = $container->get('UniteRechercheService');

        /** @var UrlHelper $urlHelper */
        $urlHelper = $container->get('ViewHelperManager')->get('Url');

        /* @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get('unicaen-app_module_options');

        $factory->setVariableService($variableService);
        $factory->setEcoleDoctoraleService($ecoleDoctoraleService);
        $factory->setUniteRechercheService($uniteRechercheService);
        $factory->setUrlHelper($urlHelper);
        $factory->setAppModuleOptions($moduleOptions);

        /** @var RoleService $roleService */
        $roleService = $container->get('RoleService');
        $factory->setApplicationRoleService($roleService);

        /** @var EmailTheseService $emailTheseService */
        $emailTheseService = $container->get(EmailTheseService::class);
        $factory->setEmailTheseService($emailTheseService);

        return $factory;
    }
}
