<?php

namespace HDR\Fieldset\Direction;

use Acteur\Hydrator\ActeurHDR\ActeurHDRHydrator;
use Acteur\Service\ActeurHDR\ActeurHDRService;
use Application\Service\Role\RoleService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Laminas\Hydrator\HydratorPluginManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Etablissement\EtablissementService;

class DirectionHydratorFactory
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DirectionHydrator
    {
        $hydrator = new DirectionHydrator();

        /** @var \Individu\Service\IndividuService $individuService */
        $individuService = $container->get(IndividuService::class);
        $hydrator->setIndividuService($individuService);

        /** @var ActeurHDRService $acteurHDRService */
        $acteurHDRService = $container->get(ActeurHDRService::class);
        $hydrator->setActeurHDRService($acteurHDRService);

        /** @var \Application\Service\Role\RoleService $roleService */
        $roleService = $container->get(RoleService::class);
        $hydrator->setApplicationRoleService($roleService);

        /** @var EtablissementService $etablissementService */
        $etablissementService = $container->get(EtablissementService::class);
        $hydrator->setEtablissementService($etablissementService);

        /** @var ActeurHDRHydrator $acteurHDRHydrator */
        $acteurHDRHydrator = $container->get(HydratorPluginManager::class)->get(ActeurHDRHydrator::class);
        $hydrator->setActeurHDRHydrator($acteurHDRHydrator);

        return $hydrator;
    }
}