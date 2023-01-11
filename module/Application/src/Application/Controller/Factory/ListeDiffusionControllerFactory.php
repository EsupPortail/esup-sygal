<?php

namespace Application\Controller\Factory;

use Application\Controller\ListeDiffusionController;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Fichier\Service\Fichier\FichierStorageService;
use Individu\Service\IndividuService;
use Application\Service\ListeDiffusion\ListeDiffusionService;
use Application\Service\Notification\NotifierService;
use Application\Service\Role\RoleService;
use Structure\Service\Structure\StructureService;
use Interop\Container\ContainerInterface;

class ListeDiffusionControllerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $controller = new ListeDiffusionController();

        /**
         * @var ListeDiffusionService $individuService
         */
        $individuService = $container->get(ListeDiffusionService::class);
        $controller->setListeDiffusionService($individuService);

        /**
         * @var IndividuService $individuService
         */
        $individuService = $container->get(IndividuService::class);
        $controller->setIndividuService($individuService);

        /**
         * @var \Fichier\Service\Fichier\FichierStorageService $fileService
         */
        $fileService = $container->get(FichierStorageService::class);
        $controller->setFichierStorageService($fileService);

        /**
         * @var NotifierService $notifierService
         */
        $notifierService = $container->get(NotifierService::class);
        $controller->setApplicationNotifierService($notifierService);

        /**
         * @var RoleService $roleService
         */
        $roleService = $container->get(RoleService::class);
        $controller->setRoleService($roleService);

        /**
         * @var EtablissementService $etablissementService
         */
        $etablissementService = $container->get(EtablissementService::class);
        $controller->setEtablissementService($etablissementService);

        /**
         * @var StructureService $structureService
         */
        $structureService = $container->get(StructureService::class);
        $controller->setStructureService($structureService);

        /**
         * @var EtablissementService $etablissementService
         */
        $ecoleDoctoraleService = $container->get(EcoleDoctoraleService::class);
        $controller->setEcoleDoctoraleService($ecoleDoctoraleService);

        return $controller;
    }
}