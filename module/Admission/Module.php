<?php

namespace Admission;

use Admission\Event\AdmissionEventListener;
use Admission\Event\Avis\AdmissionAvisEventListener;
use Admission\Event\Validation\AdmissionValidationEventListener;
use Laminas\Config\Factory as ConfigFactory;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\Glob;

class Module
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();

        $container = $e->getApplication()->getServiceManager();

//        /** @var AdmissionEventListener $admissionListener */
//        $admissionListener = $container->get(AdmissionEventListener::class);
//        $admissionListener->attach($eventManager);

        /** @var AdmissionAvisEventListener $admissionAvisListener */
        $admissionAvisListener = $container->get(AdmissionAvisEventListener::class);
        $admissionAvisListener->attach($eventManager);
        
        /** @var AdmissionValidationEventListener $admissionValidationListener */
        $admissionValidationListener = $container->get(AdmissionValidationEventListener::class);
        $admissionValidationListener->attach($eventManager);
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
}
