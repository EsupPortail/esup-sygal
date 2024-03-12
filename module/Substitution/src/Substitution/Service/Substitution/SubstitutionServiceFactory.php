<?php

namespace Substitution\Service\Substitution;

use Psr\Container\ContainerInterface;
use Substitution\Constants;
use Substitution\Service\Substitution\Doctorant\DoctorantSubstitutionService;
use Substitution\Service\Substitution\EcoleDoctorale\EcoleDoctoraleSubstitutionService;
use Substitution\Service\Substitution\Etablissement\EtablissementSubstitutionService;
use Substitution\Service\Substitution\Individu\IndividuSubstitutionService;
use Substitution\Service\Substitution\Structure\StructureSubstitutionService;
use Substitution\Service\Substitution\UniteRecherche\UniteRechercheSubstitutionService;

class SubstitutionServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): SubstitutionService
    {
        $service = new SubstitutionService();

        $service->setSpecificSubstitutionServices([
            $container->get(IndividuSubstitutionService::class),
            $container->get(DoctorantSubstitutionService::class),
            $container->get(StructureSubstitutionService::class),
            $container->get(EtablissementSubstitutionService::class),
            $container->get(EcoleDoctoraleSubstitutionService::class),
            $container->get(UniteRechercheSubstitutionService::class),
        ]);

        return $service;
    }
}