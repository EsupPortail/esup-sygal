<?php

namespace Notification\Service;

use Notification\Entity\Service\NotifEntityService;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Service\Mailer\MailerService;
use Zend\ServiceManager\ServiceLocatorInterface;

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

        /** @var NotificationRenderingService $notificationRenderer */
        $notificationRenderer = $serviceLocator->get(NotificationRenderingService::class);

        $options = $this->getOptions($serviceLocator);

        /** @var NotifierService $service */
        $service = new $notifierServiceClass($notificationRenderer);
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
