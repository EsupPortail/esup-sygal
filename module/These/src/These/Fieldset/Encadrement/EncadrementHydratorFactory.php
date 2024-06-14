<?php

namespace These\Fieldset\Encadrement;

use Application\Service\Role\RoleService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
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

        /** @var ActeurService $acteurService */
        $acteurService = $container->get(ActeurService::class);
        $hydrator->setActeurService($acteurService);

        /** @var \Application\Service\Role\RoleService $roleService */
        $roleService = $container->get(RoleService::class);
        $hydrator->setRoleService($roleService);

        return $hydrator;
    }
}