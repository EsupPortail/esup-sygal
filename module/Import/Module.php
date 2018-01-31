<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Import;

use Import\Service\FetcherService;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Config\Factory as ConfigFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        $paths = array_merge(
            [__DIR__ . '/config/module.config.php']
        );
        return ConfigFactory::fromFiles($paths);
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /*public function getControllerConfig()
    {
        return [
            'factories' => [
                'Import\Controller\Import' => function(ServiceLocatorInterface $serviceLocator) {
                    $parentLocator = $serviceLocator->getServiceLocator();
                    $fetcherService = $parentLocator->get(FetcherService::class);
                    die("coucou");
                    return new Controller\ImportController(
                            $fetcherService
                    );
                },
            ],
        ];
    }*/
}
