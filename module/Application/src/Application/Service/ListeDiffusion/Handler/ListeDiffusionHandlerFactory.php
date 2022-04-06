<?php

namespace Application\Service\ListeDiffusion\Handler;

use Application\Service\Acteur\ActeurService;
use Doctorant\Service\DoctorantService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Individu\IndividuService;
use Application\Service\ListeDiffusion\Handler;
use Application\Service\Role\RoleService;
use Application\Service\Structure\StructureService;
use Interop\Container\ContainerInterface;

class ListeDiffusionHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $service = new Handler\ListeDiffusionHandler();

        $config = $container->get('Config');
        $service->setConfig($config['liste-diffusion'] ?? []);

        /**
         * @var StructureService $structureService
         */
        $structureService = $container->get(StructureService::class);
        $service->setStructureService($structureService);

        /**
         * @var IndividuService $individuService
         */
        $individuService = $container->get(IndividuService::class);
        $service->setIndividuService($individuService);

        /**
         * @var EtablissementService $etablissementService
         */
        $etablissementService = $container->get(EtablissementService::class);
        $service->setEtablissementService($etablissementService);

        /**
         * @var ActeurService $acteurService
         */
        $acteurService = $container->get(ActeurService::class);
        $service->setActeurService($acteurService);

        /**
         * @var RoleService $roleService
         */
        $roleService = $container->get(RoleService::class);
        $service->setRoleService($roleService);

        /**
         * @var DoctorantService $doctorantService
         */
        $doctorantService = $container->get(DoctorantService::class);
        $service->setDoctorantService($doctorantService);

        return $service;
    }
}