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
        $service = new RapportActiviteAvisService();

        /** @var \UnicaenAvis\Service\AvisService $avisService */
        $avisService = $container->get(AvisService::class);
        $service->setAvisService($avisService);

        $service->setEventManager($container->get('EventManager'));

        return $service;
    }
}