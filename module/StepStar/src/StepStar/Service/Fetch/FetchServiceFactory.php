<?php

namespace StepStar\Service\Fetch;

use These\Service\These\TheseService;
use Psr\Container\ContainerInterface;

class FetchServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): FetchService
    {
        /** @var TheseService $theseService */
        $theseService = $container->get(TheseService::class);

        $service = new FetchService();
        $service->setTheseService($theseService);

        return $service;
    }
}