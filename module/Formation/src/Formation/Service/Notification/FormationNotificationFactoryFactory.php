<?php

namespace Formation\Service\Notification;

use Application\Service\ListeDiffusion\ListeDiffusionService;
use Application\Service\Role\RoleService;
use Interop\Container\ContainerInterface;
use Notification\Factory\NotificationFactoryFactory;
use UnicaenRenderer\Service\Rendu\RenduService;

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
        $factory->setRoleService($roleService);

        return $factory;
    }
}
