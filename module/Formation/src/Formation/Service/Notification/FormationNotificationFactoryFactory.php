<?php

namespace Formation\Service\Notification;

use Application\Service\ListeDiffusion\ListeDiffusionService;
use Application\Service\Role\RoleService;
use Formation\Service\Url\UrlService;
use Interop\Container\ContainerInterface;
use Notification\Factory\NotificationFactoryFactory;
use UnicaenRenderer\Service\Rendu\RenduService;
use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManager;

/**
 * @author Unicaen
 */
class FormationNotificationFactoryFactory extends NotificationFactoryFactory
{
    /**
     * @var string
     */
    protected string $class = FormationNotificationFactory::class;

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FormationNotificationFactory
    {
        /** @var FormationNotificationFactory $factory */
        $factory = parent::__invoke($container);

        /** @var RenduService $renduService */
        $renduService = $container->get(RenduService::class);
        $factory->setRenduService($renduService);

        /**
         * @var ListeDiffusionService $individuService
         */
        $listeDiffusionService = $container->get(ListeDiffusionService::class);
        $factory->setListeDiffusionService($listeDiffusionService);

        $roleService = $container->get(RoleService::class);
        $factory->setApplicationRoleService($roleService);

        /** @var TemplateVariablePluginManager $rapm */
        $rapm = $container->get(TemplateVariablePluginManager::class);
        $factory->setTemplateVariablePluginManager($rapm);

        /**
         * @var UrlService $urlService
         */
        $urlService = $container->get(UrlService::class);
        $factory->setUrlService($urlService);

        return $factory;
    }
}
