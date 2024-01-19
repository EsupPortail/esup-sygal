<?php

namespace Individu\Fieldset\IndividuRoleEtablissement;

use Psr\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;

class IndividuRoleEtablissementHydratorFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : IndividuRoleEtablissementHydrator
    {
        $hydrator = new IndividuRoleEtablissementHydrator();

        /** @var \Structure\Service\Etablissement\EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $hydrator->setEtablissementService($etablissementService);

        return $hydrator;
    }
}