<?php

namespace Notification\Service;

use Notification\Entity\Service\NotifEntityService;
use Notification\NotificationRenderer;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Service\Mailer\MailerService;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Renderer\PhpRenderer;

/**
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class NotifierServiceFactory
{
    protected $notifierServiceClass = NotifierService::class;

    /**
     * Create service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return NotifierService
     */
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        $notifierServiceClass = $this->notifierServiceClass;

        $notificationFactory = new NotificationFactory();

        /** @var MailerService $mailerService */
        $mailerService = $serviceLocator->get(MailerService::class);

        /** @var NotifEntityService $notifEntityService */
        $notifEntityService = $serviceLocator->get(NotifEntityService::class);

        /** @var PhpRenderer $viewRenderer */
        $viewRenderer = $serviceLocator->get('view_renderer');

        $renderer = new NotificationRenderer($viewRenderer);

        $options = $this->getOptions($serviceLocator);

        /** @var NotifierService $service */
        $service = new $notifierServiceClass($renderer);
        $service->setNotificationFactory($notificationFactory);
        $service->setNotifEntityService($notifEntityService);
        $service->setMailerService($mailerService);
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
