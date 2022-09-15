<?php

namespace These\Service\FichierThese;

use Interop\Container\ContainerInterface;

trait FichierTheseServiceLocateTrait
{
    /**
     * @param ContainerInterface $container
     * @return FichierTheseService
     */
    public function locateFichierTheseService(ContainerInterface $container)
    {
        /** @var FichierTheseService $service */
        $service = $container->get('FichierTheseService');

        return $service;
    }
}