<?php

namespace Admission\Service\Avis;

use Psr\Container\ContainerInterface;
use UnicaenAvis\Service\AvisService;

class AdmissionAvisServiceFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdmissionAvisService
    {
        $service = new AdmissionAvisService();

        /** @var \UnicaenAvis\Service\AvisService $avisService */
        $avisService = $container->get(AvisService::class);
        $service->setAvisService($avisService);

        $service->setEventManager($container->get('EventManager'));

        return $service;
    }
}