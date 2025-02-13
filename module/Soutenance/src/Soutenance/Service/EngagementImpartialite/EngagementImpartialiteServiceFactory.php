<?php

namespace Soutenance\Service\EngagementImpartialite;

use Acteur\Service\ActeurHDR\ActeurHDRService;
use Acteur\Service\ActeurThese\ActeurTheseService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Validation\ValidationHDR\ValidationHDRService;
use Soutenance\Service\Validation\ValidationThese\ValidationTheseService;

class EngagementImpartialiteServiceFactory
{
    /**
     * @param ContainerInterface $container
     * @return EngagementImpartialiteService
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var ValidationTheseService $validationService */
        $validationService = $container->get(ValidationTheseService::class);

        /** @var EngagementImpartialiteService $service */
        $service = new EngagementImpartialiteService();
        $service->setValidationTheseService($validationService);

        /** @var ActeurTheseService $acteurService */
        $acteurService = $container->get(ActeurTheseService::class);
        $service->setActeurTheseService($acteurService);

        /** @var ActeurHDRService $acteurHDRService */
        $acteurHDRService = $container->get(ActeurHDRService::class);
        $service->setActeurHDRService($acteurHDRService);

        /** @var ValidationHDRService $validationHDRService */
        $validationHDRService = $container->get(ValidationHDRService::class);
        $service->setValidationHDRService($validationHDRService);


        return $service;
    }
}