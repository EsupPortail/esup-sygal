<?php

namespace Depot\Service\Notification;

use Application\Service\Email\EmailTheseService;
use Application\Service\Role\RoleService;
use Application\Service\Variable\VariableService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Laminas\View\Helper\Url as UrlHelper;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\UniteRecherche\UniteRechercheService;

/**
 * @author Unicaen
 */
class NotifierServiceFactory extends \Notification\Service\NotifierServiceFactory
{
    protected string $notifierServiceClass = NotifierService::class;

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): NotifierService
    {
        /** @var NotifierService $service */
        $service = parent::__invoke($container);

        /** @var VariableService $variableService */
        $variableService = $container->get('VariableService');
        $service->setVariableService($variableService);

        /** @var EcoleDoctoraleService $ecoleDoctoraleService */
        $ecoleDoctoraleService = $container->get('EcoleDoctoraleService');
        $service->setEcoleDoctoraleService($ecoleDoctoraleService);

        /** @var UniteRechercheService $uniteRechercheService */
        $uniteRechercheService = $container->get('UniteRechercheService');
        $service->setUniteRechercheService($uniteRechercheService);

        /** @var IndividuService $individuService */
        $individuService = $container->get(IndividuService::class);
        $service->setIndividuService($individuService);

        /** @var RoleService $roleService */
        $roleService = $container->get('RoleService');
        $service->setRoleService($roleService);

        /** @var UrlHelper $urlHelper */
        $urlHelper = $container->get('ViewHelperManager')->get('Url');
        $service->setUrlHelper($urlHelper);

        /** @var NotificationFactory $notificationFactory */
        $notificationFactory = $container->get(NotificationFactory::class);
        $service->setNotificationFactory($notificationFactory);

        /** @var EmailTheseService $emailTheseService */
        $emailTheseService = $container->get(EmailTheseService::class);
        $service->setEmailTheseService($emailTheseService);

        return $service;
    }
}
