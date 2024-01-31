<?php

namespace Admission\Service\Inscription;

use Admission\Service\Verification\VerificationService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class InscriptionServiceFactory {

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        $verificationService = $container->get(VerificationService::class);

        $service = new InscriptionService();
        $service->setVerificationService($verificationService);
        return $service;
    }
}