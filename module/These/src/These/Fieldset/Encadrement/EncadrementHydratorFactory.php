<?php

namespace These\Fieldset\Encadrement;

use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Qualite\QualiteService;
use Structure\Service\Etablissement\EtablissementService;
use These\Service\Acteur\ActeurService;

class EncadrementHydratorFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EncadrementHydrator
    {
        $hydrator = new EncadrementHydrator();

        /** @var \Individu\Service\IndividuService $individuService */
        $individuService = $container->get(IndividuService::class);
        $hydrator->setIndividuService($individuService);

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $hydrator->setEtablissementService($etablissementService);

        /** @var ActeurService $acteurService */
        $acteurService = $container->get(ActeurService::class);
        $hydrator->setActeurService($acteurService);

        /** @var QualiteService $qualiteService */
        $qualiteService = $container->get(QualiteService::class);
        $hydrator->setQualiteService($qualiteService);

        return $hydrator;
    }
}