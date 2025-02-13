<?php

namespace These\Fieldset\Encadrement;

use Application\Service\Role\RoleService;
use Doctrine\ORM\EntityManager;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Acteur\Service\ActeurThese\ActeurTheseService;

class EncadrementHydratorFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EncadrementHydrator
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('Doctrine\ORM\EntityManager');
        $hydrator = new EncadrementHydrator($entityManager);

        /** @var \Individu\Service\IndividuService $individuService */
        $individuService = $container->get(IndividuService::class);
        $hydrator->setIndividuService($individuService);

        /** @var ActeurTheseService $acteurService */
        $acteurService = $container->get(ActeurTheseService::class);
        $hydrator->setActeurTheseService($acteurService);

        /** @var \Application\Service\Role\RoleService $roleService */
        $roleService = $container->get(RoleService::class);
        $hydrator->setApplicationRoleService($roleService);

        return $hydrator;
    }
}