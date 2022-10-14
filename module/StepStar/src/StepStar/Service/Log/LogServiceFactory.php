<?php

namespace StepStar\Service\Log;

use These\Service\These\TheseService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class LogServiceFactory implements FactoryInterface
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LogService
    {
        /** @var TheseService $theseService */
        $theseService = $container->get(TheseService::class);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');

        $service = new LogService();
        $service->setTheseService($theseService);
        $service->setObjectManager($em);

        return $service;
    }
}