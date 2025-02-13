<?php

namespace Acteur\Hydrator\ActeurThese;

use Individu\Hydrator\Strategy\IndividuStrategy;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Qualite\QualiteService;
use Structure\Hydrator\Strategy\EcoleDoctoraleStrategy;
use Structure\Hydrator\Strategy\EtablissementStrategy;
use Structure\Hydrator\Strategy\UniteRechercheStrategy;

class ActeurTheseHydratorFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ActeurTheseHydrator
    {
        $hydrator = new ActeurTheseHydrator();

        $hydrator->addStrategy('individu', $container->get(IndividuStrategy::class));
        $hydrator->addStrategy('etablissement', $container->get(EtablissementStrategy::class));
        $hydrator->addStrategy('ecoleDoctorale', $container->get(EcoleDoctoraleStrategy::class));
        $hydrator->addStrategy('uniteRecherche', $container->get(UniteRechercheStrategy::class));

        /** @var QualiteService $qualiteService */
        $qualiteService = $container->get(QualiteService::class);
        $hydrator->setQualiteService($qualiteService);

        return $hydrator;
    }
}