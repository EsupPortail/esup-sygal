<?php

namespace Application\Service\Rapport\Avis;

use Structure\Service\Etablissement\EtablissementService;
use Interop\Container\ContainerInterface;

class RapportAvisServiceFactory
{
    public function __invoke(ContainerInterface $container): RapportAvisService
    {
        /**
         * @var EtablissementService $etablissementService
         */
        $etablissementService = $container->get('EtablissementService');

        $service = new RapportAvisService();

        $service->setEtablissementService($etablissementService);

        return $service;
    }
}