<?php

namespace Application\Service\Notification;

use Application\Service\MailerService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Renderer\RendererInterface;

/**
 *
 *
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class NotificationServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return NotificationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $mailerService MailerService */
        $mailerService = $serviceLocator->get('UnicaenApp\Service\Mailer');

        /* @var $renderer RendererInterface */
        $renderer = $serviceLocator->get('view_renderer');

        $options = $this->getOptions($serviceLocator);

        $service = new NotificationService($mailerService, $renderer);
        $service->setOptions($options);

        return $service;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return array
     */
    private function getOptions(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        if (! isset($config['sodoct']['notification'])) {
            return [];
        }

        return $config['sodoct']['notification'];
    }
}
