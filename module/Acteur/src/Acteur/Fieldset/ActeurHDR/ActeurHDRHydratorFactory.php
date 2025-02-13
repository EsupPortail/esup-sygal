<?php

namespace Acteur\Fieldset\ActeurHDR;

use Interop\Container\ContainerInterface;
use Soutenance\Service\Qualite\QualiteService;

class ActeurHDRHydratorFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : ActeurHDRHydrator
    {


//        $hydrator->addStrategy('individu', $container->get(IndividuStrategy::class));
//        $hydrator->addStrategy('etablissement', $container->get(EtablissementStrategy::class));
//        $hydrator->addStrategy('ecoleDoctorale', $container->get(EcoleDoctoraleStrategy::class));
//        $hydrator->addStrategy('uniteRecherche', $container->get(UniteRechercheStrategy::class));
        $hydrator = new ActeurHDRHydrator($container->get('doctrine.entitymanager.orm_default'));
        /** @var QualiteService $qualiteService */
        $qualiteService = $container->get(QualiteService::class);
        $hydrator->setQualiteService($qualiteService);
        return $hydrator;
    }
}