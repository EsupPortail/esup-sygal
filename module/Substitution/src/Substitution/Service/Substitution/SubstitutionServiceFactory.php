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
            Constants::TYPE_individu => $container->get(IndividuSubstitutionService::class),
            Constants::TYPE_doctorant => $container->get(DoctorantSubstitutionService::class),
            Constants::TYPE_structure => $container->get(StructureSubstitutionService::class),
            Constants::TYPE_etablissement => $container->get(EtablissementSubstitutionService::class),
            Constants::TYPE_ecole_doct => $container->get(EcoleDoctoraleSubstitutionService::class),
            Constants::TYPE_unite_rech => $container->get(UniteRechercheSubstitutionService::class),
        ]);

        return $service;
    }
}