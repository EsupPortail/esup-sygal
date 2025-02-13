<?php

namespace These\Fieldset\Direction;

use Acteur\Hydrator\ActeurThese\ActeurTheseHydrator;
use Acteur\Service\ActeurThese\ActeurTheseService;
use Application\Service\Role\RoleService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Laminas\Hydrator\HydratorPluginManager;
use Structure\Service\Etablissement\EtablissementService;

class DirectionHydratorFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DirectionHydrator
    {
        $hydrator = new DirectionHydrator();

        /** @var \Individu\Service\IndividuService $individuService */
        $individuService = $container->get(IndividuService::class);
        $hydrator->setIndividuService($individuService);

        /** @var ActeurTheseService $acteurService */
        $acteurService = $container->get(ActeurTheseService::class);
        $hydrator->setActeurTheseService($acteurService);

        /** @var \Application\Service\Role\RoleService $roleService */
        $roleService = $container->get(RoleService::class);
        $hydrator->setApplicationRoleService($roleService);

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $hydrator->setEtablissementService($etablissementService);

        /** @var \Acteur\Hydrator\ActeurThese\ActeurTheseHydrator $acteurHydrator */
        $acteurHydrator = $container->get(HydratorPluginManager::class)->get(ActeurTheseHydrator::class);
        $hydrator->setActeurTheseHydrator($acteurHydrator);

        return $hydrator;
    }
}