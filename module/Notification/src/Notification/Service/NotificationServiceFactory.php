<?php

namespace Notification\Service;

use Notification\Service\Mailer\MailerService;
use UnicaenApp\Exception\LogicException;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\Url;
use Zend\View\Renderer\RendererInterface;

/**
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class NotificationServiceFactory
{
    protected $notificationServiceClass = NotificationService::class;

    /**
     * Create service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return NotificationService
     */
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        $class = $this->notificationServiceClass;

        /** @var MailerService $mailerService */
        $mailerService = $serviceLocator->get(MailerService::class);

        /** @var RouteStackInterface $router */
        $router = $serviceLocator->get('router');
        /* @var Url $urlHelper */
        $urlHelper = $serviceLocator->get('ViewHelperManager')->get('Url');
        $urlHelper->setRouter($router);

        /* @var $renderer RendererInterface */
        $renderer = $serviceLocator->get('view_renderer');

        $options = $this->getOptions($serviceLocator);

        /** @var NotificationService $service */
        $service = new $class($renderer);
        $service->setMailerService($mailerService);
        $service->setOptions($options);
        $service->setUrlHelper($urlHelper);

        return $service;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return array
     */
    private function getOptions(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        if (! array_key_exists($key = 'notification', $config)) {
            throw new LogicException(
                "Les options du module de notification doivent être specifiées via la clé '$key' à la racine de la configuration");
        }
        if (! is_array($config['notification'])) {
            throw new LogicException(
                "Les options du module de notification doivent être specifiées par un tableau");
        }

        return $config['notification'];
    }
}
