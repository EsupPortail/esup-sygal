<?php

namespace Substitution\Service\Substitution\Individu;

use Individu\Service\IndividuService;
use Psr\Container\ContainerInterface;

class IndividuSubstitutionServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): IndividuSubstitutionService
    {
        $service = new IndividuSubstitutionService();

        /** @var \Individu\Service\IndividuService $entityService */
        $entityService = $container->get(IndividuService::class);
        $service->setEntityService($entityService);

        return $service;
    }
}