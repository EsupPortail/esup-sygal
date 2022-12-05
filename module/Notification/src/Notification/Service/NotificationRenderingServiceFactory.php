<?php

namespace Notification\Service;

use Interop\Container\ContainerInterface;

/**
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class NotificationRenderingServiceFactory
{
    /**
     * Create service.
     *
     * @param ContainerInterface $container
     * @return NotificationRenderingService
     */
    public function __invoke(ContainerInterface $container)
    {
//        /** @var HttpViewManager|ConsoleViewManager $viewManager */
//        $viewManager = $container->get('ViewManager');
//        $viewRenderer = $viewManager->getRenderer();
        $viewRenderer = $container->get('ViewRenderer');

        return new NotificationRenderingService($viewRenderer);
    }
}
