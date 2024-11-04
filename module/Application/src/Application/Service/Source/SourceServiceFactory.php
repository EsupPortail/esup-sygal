<?php

namespace Application\Service\Source;

use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;

/**
 * @author Unicaen
 */
class SourceServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): SourceService
    {
        $service = new SourceService;

        $helper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($helper);

        return $service;
    }
}