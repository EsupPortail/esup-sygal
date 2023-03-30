<?php

namespace Soutenance\Service\Horodatage;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Service\Proposition\PropositionService;

class HorodatageServiceFactory {

    /**
     * @param ContainerInterface $container
     * @return HorodatageService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : HorodatageService
    {
        /**
         * @var \Horodatage\Service\Horodatage\HorodatageService $horodatageService
         * @var PropositionService $propositionService
         */
        $horodatageService = $container->get(\Horodatage\Service\Horodatage\HorodatageService::class);
        $propositionService = $container->get(PropositionService::class);

        $service = new HorodatageService();
        $service->setHorodatageService($horodatageService);
        $service->setPropositionService($propositionService);
        return $service;
    }
}