<?php

namespace Soutenance\Service\Notification;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Service\Url\UrlService;
use UnicaenRenderer\Service\Rendu\RenduService;

class NotificationServiceFactory  extends \Notification\Service\NotifierServiceFactory
{
    protected string $notifierServiceClass = NotificationService::class;

    /**
     * @param ContainerInterface $container
     * @return NotificationService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : NotificationService
    {
        /** @var NotificationService $service */
        $service = parent::__invoke($container);

        /**
         * @var RenduService $renduService
         * @var UrlService $urlService
         */
        $renduService = $container->get(RenduService::class);
        $urlService = $container->get(UrlService::class);

        $service->setRenduService($renduService);
        $service->setUrlService($urlService);
        return $service;
    }
}