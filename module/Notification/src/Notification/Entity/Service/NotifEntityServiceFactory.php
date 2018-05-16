<?php

namespace Notification\Entity\Service;

use Doctrine\ORM\EntityManager;
use UnicaenApp\Service\MessageCollector;
use Zend\ServiceManager\ServiceLocatorInterface;

class NotifEntityServiceFactory
{
    /**
     * @param ServiceLocatorInterface $sl
     * @return NotifEntityService
     */
    public function __invoke(ServiceLocatorInterface $sl)
    {
        /** @var MessageCollector $messageCollector */
        $messageCollector = $sl->get('MessageCollector');

        /** @var EntityManager $em */
        $em = $sl->get(EntityManager::class);

        $service = new NotifEntityService();
        $service->setEntityManager($em);
        $service->setServiceMessageCollector($messageCollector);

        return $service;
    }
}