<?php

namespace Depot\Service\Notification;

use Application\Service\Email\EmailTheseService;
use Application\Service\Variable\VariableService;
use Interop\Container\ContainerInterface;
use Laminas\View\Helper\Url as UrlHelper;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\UniteRecherche\UniteRechercheService;
use UnicaenApp\Options\ModuleOptions;

/**
 * @author Unicaen
 */
class NotificationFactoryFactory extends \Notification\Service\NotificationFactoryFactory
{
    /**
     * @var string
     */
    protected string $class = NotificationFactory::class;

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): NotificationFactory
    {
        /** @var NotificationFactory $factory */
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

        /** @var EmailTheseService $emailTheseService */
        $emailTheseService = $container->get(EmailTheseService::class);
        $factory->setEmailTheseService($emailTheseService);

        return $factory;
    }
}
