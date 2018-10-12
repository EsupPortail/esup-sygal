<?php

namespace Application\Service\Notification;

use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\Service\Variable\VariableService;
use UnicaenApp\Options\ModuleOptions;
use Zend\Mvc\View\Console\ViewManager as ConsoleViewManager;
use Zend\Mvc\View\Http\ViewManager as HttpViewManager;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\Url as UrlHelper;

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
     * @param ServiceLocatorInterface $serviceLocator
     * @return NotificationFactory
     */
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /** @var NotificationFactory $factory */
        $factory = parent::__invoke($serviceLocator);

        /**
         * @var VariableService       $variableService
         * @var EcoleDoctoraleService $ecoleDoctoraleService
         * @var UniteRechercheService $uniteRechercheService
         */
        $variableService = $serviceLocator->get('VariableService');
        $ecoleDoctoraleService = $serviceLocator->get('EcoleDoctoraleService');
        $uniteRechercheService = $serviceLocator->get('UniteRechercheService');

        /** @var HttpViewManager|ConsoleViewManager $vm */
        $vm = $serviceLocator->get('ViewManager');
        /** @var UrlHelper $urlHelper */
        $urlHelper = $vm->getHelperManager()->get('Url');

        /* @var ModuleOptions $moduleOptions */
        $moduleOptions = $serviceLocator->get('unicaen-app_module_options');

        $factory->setVariableService($variableService);
        $factory->setEcoleDoctoraleService($ecoleDoctoraleService);
        $factory->setUniteRechercheService($uniteRechercheService);
        $factory->setUrlHelper($urlHelper);
        $factory->setAppModuleOptions($moduleOptions);

        return $factory;
    }
}
