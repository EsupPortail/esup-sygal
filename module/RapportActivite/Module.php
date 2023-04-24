<?php

namespace RapportActivite;

use Laminas\Mvc\MvcEvent;
use RapportActivite\Event\Avis\RapportActiviteAvisEventListener;
use RapportActivite\Event\RapportActiviteEventListener;
use RapportActivite\Event\Validation\RapportActiviteValidationEventListener;

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

        /** @var \RapportActivite\Event\RapportActiviteEventListener $rapportActiviteListener */
        $rapportActiviteListener = $container->get(RapportActiviteEventListener::class);
        $rapportActiviteListener->attach($eventManager);

        /** @var \RapportActivite\Event\Avis\RapportActiviteAvisEventListener $rapportActiviteAvisListener */
        $rapportActiviteAvisListener = $container->get(RapportActiviteAvisEventListener::class);
        $rapportActiviteAvisListener->attach($eventManager);

        /** @var \RapportActivite\Event\Validation\RapportActiviteValidationEventListener $rapportActiviteValidationListener */
        $rapportActiviteValidationListener = $container->get(RapportActiviteValidationEventListener::class);
        $rapportActiviteValidationListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig(): array
    {
        return array(
            'Laminas\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
