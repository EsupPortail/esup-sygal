<?php

namespace Depot\Service\Notification;

use Application\Service\Email\EmailService;
use Application\Service\Role\RoleService;
use Application\Service\Url\UrlService;
use Application\Service\Variable\VariableService;
use Interop\Container\ContainerInterface;
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

        /** @var UrlService $urlService */
        $urlService = $container->get(UrlService::class);
        $factory->setUrlService($urlService);

        /* @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get('unicaen-app_module_options');

        $factory->setVariableService($variableService);
        $factory->setEcoleDoctoraleService($ecoleDoctoraleService);
        $factory->setUniteRechercheService($uniteRechercheService);
        $factory->setAppModuleOptions($moduleOptions);

        /** @var RoleService $roleService */
        $roleService = $container->get('RoleService');
        $factory->setApplicationRoleService($roleService);

        /** @var EmailService $emailService */
        $emailService = $container->get(EmailService::class);
        $factory->setEmailService($emailService);

        return $factory;
    }
}
