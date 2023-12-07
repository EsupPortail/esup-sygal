<?php

namespace Substitution\Service\Substitution\Etablissement;

use Psr\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;

class EtablissementSubstitutionServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EtablissementSubstitutionService
    {
        $service = new EtablissementSubstitutionService();

        /** @var \Structure\Service\Etablissement\EtablissementService $entityService */
        $entityService = $container->get(EtablissementService::class);
        $service->setEntityService($entityService);

        return $service;
    }
}