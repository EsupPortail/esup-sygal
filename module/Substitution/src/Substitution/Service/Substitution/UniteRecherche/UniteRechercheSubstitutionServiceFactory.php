<?php

namespace Substitution\Service\Substitution\UniteRecherche;

use Psr\Container\ContainerInterface;
use Structure\Service\UniteRecherche\UniteRechercheService;

class UniteRechercheSubstitutionServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): UniteRechercheSubstitutionService
    {
        $service = new UniteRechercheSubstitutionService();

        /** @var \Structure\Service\UniteRecherche\UniteRechercheService $entityService */
        $entityService = $container->get(UniteRechercheService::class);
        $service->setEntityService($entityService);

        return $service;
    }
}