<?php

namespace Application;

use Application\Event\UserAuthenticatedEventListener;
use Application\View\Helper\Navigation\MenuSecondaire;
use Laminas\Config\Factory as ConfigFactory;
use Laminas\EventManager\EventInterface;
use Laminas\Http\Request as HttpRequest;
use Laminas\ModuleManager\Feature\AutoloaderProviderInterface;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\Glob;
use Laminas\View\Helper\Navigation;
use Laminas\View\HelperPluginManager;

class Module implements BootstrapListenerInterface, AutoloaderProviderInterface, ConfigProviderInterface
{
    /**
     * @param MvcEvent $e
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function onBootstrap(EventInterface $e)
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
        $eventManager->getSharedManager()->attach('Laminas\Mvc\Controller\AbstractActionController', 'dispatch',
            function (MvcEvent $e) {
                $request = $e->getRequest();
                if ($request instanceof HttpRequest && $request->isXmlHttpRequest()) {
                    $e->getTarget()->layout('layout/ajax.phtml');
                }
            }
        );

        /* Détournement de requête pour demander la saisie du persopass */
        $deflector = new SaisieEmailContactRouteDeflector('#^home|these|soutenance|formation\/.+#', [
            'options' => ['name' => 'doctorant/modifier-email-contact'],
            'params' => ['detournement' => 1]
        ]);
        $deflector->attach($eventManager);

        $sm = $application->getServiceManager();

        /** @var RouteMatchInjector $routeMatchInjector */
        $routeMatchInjector = $sm->get('RouteMatchInjector');
        $routeMatchInjector->attach($eventManager);

        /** @var UserAuthenticatedEventListener $listener */
        $listener = $sm->get('UserAuthenticatedEventListener');
        $listener->attach($eventManager);

        /* @var $vhm HelperPluginManager */
        $vhm = $sm->get('ViewHelperManager');
        /* @var $nvh Navigation */
        $nvh = $vhm->get('Laminas\View\Helper\Navigation');
        $nvh->getPluginManager()->setInvokableClass('menuSecondaire', MenuSecondaire::class);
    }

    public function getConfig()
    {
        $paths = array_merge(
            [__DIR__ . '/config/module.config.php'],
            Glob::glob(__DIR__ . '/config/others/{,*.}{config}.php', Glob::GLOB_BRACE)
        );

        return ConfigFactory::fromFiles($paths);
    }

    public function getAutoloaderConfig(): array
    {
        return [
            'Laminas\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    public function getConsoleUsage(): array
    {
        return [
            'fichier fusionner --these= --versionFichier= [--removeFirstPage] [--notifier=]' =>
                'Fusionner la page de couverture générée avec le manuscrit',
            ['--these',           "Id de la thèse concernée"],
            ['--versionFichier',  "Version du fichier de thèse à utiliser (ex: 'VA', 'VOC')"],
            ['--removeFirstPage', "(facultatif) Témoin indiquant si la première page doit être retirée avant la fusion"],
            ['--notifier',        "(facultatif) Adresses électroniques auxquelles envoyer un courriel une fois la fusion terminée"],

            'transfer-these-data --source-id= --destination-id=' =>
                "Transférer toutes les données saisies sur une thèse *historisée* vers une autre thèse",
            ['--source-id',       "Id de la thèse historisée source"],
            ['--destination-id',  "Id de la thèse destination"],
        ];
    }
}
