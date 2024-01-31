<?php

namespace Admission\Service\ConventionFormationDoctorale;

use Interop\Container\ContainerInterface;

class ConventionFormationDoctoraleServiceFactory {

    public function __invoke(ContainerInterface $container): ConventionFormationDoctoraleService
    {

        $service = new ConventionFormationDoctoraleService();

        return $service;
    }
}