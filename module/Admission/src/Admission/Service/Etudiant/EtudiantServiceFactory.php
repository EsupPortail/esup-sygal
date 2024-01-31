<?php

namespace Admission\Service\Etudiant;

use Admission\Service\Verification\VerificationService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class EtudiantServiceFactory {

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EtudiantService
    {
        $verificationService = $container->get(VerificationService::class);

        $service = new EtudiantService();
        $service->setVerificationService($verificationService);
        return $service;
    }
}