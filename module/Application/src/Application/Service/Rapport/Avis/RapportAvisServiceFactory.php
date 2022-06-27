<?php

namespace Application\Service\Rapport\Avis;

use Structure\Service\Etablissement\EtablissementService;
use Application\Service\Notification\NotifierService;
use Interop\Container\ContainerInterface;

class RapportAvisServiceFactory
{
    public function __invoke(ContainerInterface $container): RapportAvisService
    {
        /**
         * @var EtablissementService $etablissementService
         * @var NotifierService $notifierService
         */
        $etablissementService = $container->get('EtablissementService');
        $notifierService = $container->get(NotifierService::class);

        $service = new RapportAvisService();

        $service->setEtablissementService($etablissementService);
        $service->setNotifierService($notifierService);

        return $service;
    }
}