<?php

namespace Application;

use Application\Event\UserAuthenticatedEventListener;
use Application\Event\UserRoleSelectedEventListener;
use Zend\Config\Factory as ConfigFactory;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\Glob;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $application = $e->getApplication();
        $application->getServiceManager()->get('translator');
        $eventManager = $application->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

// TODO decommenter cela avec la bonne route
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, function(MvcEvent $e) {
            $lang = $e->getRouteMatch()->getParam('language');
            \Locale::setDefault($lang);
        });

        /* Utilise un layout spécial si on est en AJAX. Valable pour TOUS les modules de l'application */
        $eventManager->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch',
            function (MvcEvent $e) {
                $request = $e->getRequest();
                if ($request instanceof HttpRequest && $request->isXmlHttpRequest()) {
                    $e->getTarget()->layout('layout/ajax.phtml');
                }
            }
        );

        /* Détournement de requête pour demander la saisie du persopass */
        $deflector = new SaisiePersopassRouteDeflector('#^home|these\/.+#', [
            'options' => ['name' => 'doctorant/modifier-persopass'],
            'params' => ['detournement' => 1]
        ]);
        $deflector->attach($eventManager);

        $sm = $application->getServiceManager();

        /** @var RouteMatchInjector $routeMatchInjector */
        $routeMatchInjector = $sm->get('RouteMatchInjector');
        $eventManager->attachAggregate($routeMatchInjector);

        /** @var UserAuthenticatedEventListener $listener */
        $listener = $sm->get('UserAuthenticatedEventListener');
        $listener->attach($eventManager);

        /** @var UserRoleSelectedEventListener $listener */
        $listener = $sm->get('UserRoleSelectedEventListener');
        $listener->attach($eventManager);
    }

    public function getConfig()
    {
        $paths = array_merge(
            [__DIR__ . '/config/module.config.php'],
            Glob::glob(__DIR__ . '/config/others/{,*.}{config}.php', Glob::GLOB_BRACE)
        );

        return ConfigFactory::fromFiles($paths);
    }

    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }
}
