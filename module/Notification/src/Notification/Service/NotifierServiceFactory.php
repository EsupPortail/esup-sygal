<?php

namespace Notification\Service;

use Interop\Container\ContainerInterface;
use Notification\Entity\Service\NotifEntityService;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Service\Mailer\MailerService;

/**
 * @author Bertrand GAUTHIER <bertrand.gauthier at unicaen.fr>
 */
class NotifierServiceFactory
{
    protected $notifierServiceClass = NotifierService::class;

    /**
     * Create service.
     *
     * @param ContainerInterface $container
     * @return NotifierService
     */
    public function __invoke(ContainerInterface $container)
    {
        $notifierServiceClass = $this->notifierServiceClass;

        $notificationFactory = new NotificationFactory();

        /** @var MailerService $mailerService */
        $mailerService = $container->get(MailerService::class);

        /** @var NotifEntityService $notifEntityService */
        $notifEntityService = $container->get(NotifEntityService::class);

        /** @var NotificationRenderingService $notificationRenderer */
        $notificationRenderer = $container->get(NotificationRenderingService::class);

        $options = $this->getOptions($container);

        /** @var NotifierService $service */
        $service = new $notifierServiceClass($notificationRenderer);
        $service->setNotificationFactory($notificationFactory);
        $service->setNotifEntityService($notifEntityService);
        $service->setMailerService($mailerService);
        $service->setOptions($options);

        return $service;
    }

    /**
     * @param ContainerInterface $container
     * @return array
     */
    private function getOptions(ContainerInterface $container)
    {
        $config = $container->get('config');

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
