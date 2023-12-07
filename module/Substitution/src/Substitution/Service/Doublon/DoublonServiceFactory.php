<?php

namespace Substitution\Service\Doublon;

use Psr\Container\ContainerInterface;
use Substitution\Constants;
use Substitution\Service\Doublon\Doctorant\DoctorantDoublonService;
use Substitution\Service\Doublon\EcoleDoctorale\EcoleDoctoraleDoublonService;
use Substitution\Service\Doublon\Etablissement\EtablissementDoublonService;
use Substitution\Service\Doublon\Individu\IndividuDoublonService;
use Substitution\Service\Doublon\Structure\StructureDoublonService;
use Substitution\Service\Doublon\UniteRecherche\UniteRechercheDoublonService;

class DoublonServiceFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DoublonService
    {
        $service = new DoublonService;

        $service->setSpecificServices([
            Constants::TYPE_individu => $container->get(IndividuDoublonService::class),
            Constants::TYPE_doctorant => $container->get(DoctorantDoublonService::class),
            Constants::TYPE_structure => $container->get(StructureDoublonService::class),
            Constants::TYPE_etablissement => $container->get(EtablissementDoublonService::class),
            Constants::TYPE_ecole_doct => $container->get(EcoleDoctoraleDoublonService::class),
            Constants::TYPE_unite_rech => $container->get(UniteRechercheDoublonService::class),
        ]);

        return $service;
    }
}