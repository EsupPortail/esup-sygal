<?php

namespace Application\Service\Notification;

use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\Service\Variable\VariableService;
use Interop\Container\ContainerInterface;
use UnicaenApp\Options\ModuleOptions;
use Laminas\Mvc\Console\View\ViewManager as ConsoleViewManager;
use Laminas\Mvc\View\Http\ViewManager as HttpViewManager;
use Laminas\View\Helper\Url as UrlHelper;

/**
 * @author Unicaen
 */
class NotificationFactoryFactory extends \Notification\Service\NotificationFactoryFactory
{
    /**
     * @var string
     */
    protected $class = NotificationFactory::class;

    /**
     * Create service.
     *
     * @param ContainerInterface $container
     * @return NotificationFactory
     */
    public function __invoke(ContainerInterface $container)
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

        /** @var HttpViewManager|ConsoleViewManager $vm */
        $vm = $container->get('ViewManager');
        /** @var UrlHelper $urlHelper */
//        $urlHelper = $vm->getHelperManager()->get('Url');
        $urlHelper = $container->get('ViewHelperManager')->get('Url');

        /* @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get('unicaen-app_module_options');

        $factory->setVariableService($variableService);
        $factory->setEcoleDoctoraleService($ecoleDoctoraleService);
        $factory->setUniteRechercheService($uniteRechercheService);
        $factory->setUrlHelper($urlHelper);
        $factory->setAppModuleOptions($moduleOptions);

        return $factory;
    }
}
