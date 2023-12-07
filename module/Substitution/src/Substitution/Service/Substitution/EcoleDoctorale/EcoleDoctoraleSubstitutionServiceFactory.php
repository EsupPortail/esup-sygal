<?php

namespace Substitution\Service\Substitution\EcoleDoctorale;

use Psr\Container\ContainerInterface;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;

class EcoleDoctoraleSubstitutionServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EcoleDoctoraleSubstitutionService
    {
        $service = new EcoleDoctoraleSubstitutionService();

        /** @var \Structure\Service\EcoleDoctorale\EcoleDoctoraleService $entityService */
        $entityService = $container->get(EcoleDoctoraleService::class);
        $service->setEntityService($entityService);

        return $service;
    }
}