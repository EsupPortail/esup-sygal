<?php

namespace Application\Service\Etablissement;

use Interop\Container\ContainerInterface;

trait EtablissementServiceLocateTrait
{
    /**
     * @param ContainerInterface $container
     * @return EtablissementService
     */
    public function locateEtablissementService(ContainerInterface $container)
    {
        /** @var EtablissementService $service */
        $service = $container->get('EtablissementService');

        return $service;
    }
}