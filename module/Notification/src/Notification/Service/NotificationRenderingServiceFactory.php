<?php

namespace Notification\Service;

use Zend\Mvc\View\Console\ViewManager as ConsoleViewManager;
use Zend\Mvc\View\Http\ViewManager as HttpViewManager;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class NotificationRenderingServiceFactory
{
    /**
     * Create service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return NotificationRenderingService
     */
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        /** @var HttpViewManager|ConsoleViewManager $viewManager */
        $viewManager = $serviceLocator->get('ViewManager');
        $viewRenderer = $viewManager->getRenderer();

        $notificationRenderer = new NotificationRenderingService($viewRenderer);

        return $notificationRenderer;
    }
}
