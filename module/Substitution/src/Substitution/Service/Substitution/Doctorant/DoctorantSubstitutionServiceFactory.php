<?php

namespace Substitution\Service\Substitution\Doctorant;

use Doctorant\Service\DoctorantService;
use Psr\Container\ContainerInterface;

class DoctorantSubstitutionServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DoctorantSubstitutionService
    {
        $service = new DoctorantSubstitutionService();

        /** @var \Individu\Service\IndividuService $entityService */
        $entityService = $container->get(DoctorantService::class);
        $service->setEntityService($entityService);

        return $service;
    }
}