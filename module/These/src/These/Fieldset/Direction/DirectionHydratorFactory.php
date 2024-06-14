<?php

namespace These\Fieldset\Direction;

use Application\Service\Role\RoleService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Laminas\Hydrator\HydratorPluginManager;
use These\Hydrator\ActeurHydrator;
use These\Service\Acteur\ActeurService;

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

        /** @var ActeurService $acteurService */
        $acteurService = $container->get(ActeurService::class);
        $hydrator->setActeurService($acteurService);

        /** @var \Application\Service\Role\RoleService $roleService */
        $roleService = $container->get(RoleService::class);
        $hydrator->setRoleService($roleService);

        /** @var \These\Hydrator\ActeurHydrator $acteurHydrator */
        $acteurHydrator = $container->get(HydratorPluginManager::class)->get(ActeurHydrator::class);
        $hydrator->setActeurHydrator($acteurHydrator);

        return $hydrator;
    }
}