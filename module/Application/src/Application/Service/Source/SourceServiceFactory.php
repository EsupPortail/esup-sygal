<?php

namespace Application\Service\Source;

use Interop\Container\ContainerInterface;

/**
 * @author Unicaen
 */
class SourceServiceFactory
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return SourceService
     */
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $service = new SourceService;

        /* Injectez vos dépendances ICI */

        return $service;
    }
}