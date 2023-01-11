<?php

namespace Formation\Service\Notification;

use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnicaenRenderer\Service\Rendu\RenduService;

class NotifierServiceFactory extends \Notification\Service\NotifierServiceFactory
{
    protected string $notifierServiceClass = NotifierService::class;

    /**
     * @param ContainerInterface $container
     * @return NotifierService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : NotifierService
    {
        /**
         * @var NotifierService $service
         * @var RenduService $renduService
         */
        $service = parent::__invoke($container);
        $renduService = $container->get(RenduService::class);

        $service->setRenduService($renduService);

        return $service;
    }
}