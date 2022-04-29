<?php

namespace RapportActivite\Service\Avis;

use Psr\Container\ContainerInterface;
use UnicaenAvis\Service\AvisService;

class RapportActiviteAvisServiceFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteAvisService
    {
        $etablissementService = $container->get('EtablissementService');
        $avisService = $container->get(AvisService::class);

        $service = new RapportActiviteAvisService();

        $service->setEtablissementService($etablissementService);
        $service->setAvisService($avisService);

        return $service;
    }
}