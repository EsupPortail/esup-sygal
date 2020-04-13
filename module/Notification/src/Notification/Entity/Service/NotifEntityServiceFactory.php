<?php

namespace Notification\Entity\Service;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use UnicaenApp\Service\MessageCollector;

class NotifEntityServiceFactory
{
    /**
     * @param ContainerInterface $container
     * @return NotifEntityService
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var MessageCollector $messageCollector */
        $messageCollector = $container->get('MessageCollector');

        /** @var EntityManager $em */
        $em = $container->get(EntityManager::class);

        $service = new NotifEntityService();
        $service->setEntityManager($em);
        $service->setServiceMessageCollector($messageCollector);

        return $service;
    }
}