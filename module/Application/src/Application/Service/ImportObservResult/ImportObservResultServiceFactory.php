<?php

namespace Application\Service\ImportObservResult;

use Application\Entity\Db\ImportObservResult;
use Application\Entity\Db\Repository\ImportObservResultRepository;
use Application\Service\Notification\NotificationService;
use Application\Service\These\TheseService;
use Zend\ServiceManager\ServiceLocatorInterface;

class ImportObservResultServiceFactory
{
    public function __invoke(ServiceLocatorInterface $sl)
    {
        /** @var ImportObservResultRepository $repo */
        $repo = $sl->get('doctrine.entitymanager.orm_default')->getRepository(ImportObservResult::class);

        /**
         * @var TheseService $theseService
         * @var NotificationService $notificationService
         */
        $theseService = $sl->get('TheseService');
        $notificationService = $sl->get('NotificationService');

        $service = new ImportObservResultService();
        $service->setRepository($repo);
        $service->setTheseService($theseService);
        $service->setNotificationService($notificationService);

        return $service;
    }
}