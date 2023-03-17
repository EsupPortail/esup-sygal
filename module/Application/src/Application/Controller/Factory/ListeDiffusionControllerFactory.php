<?php

namespace Application\Controller\Factory;

use Application\Controller\ListeDiffusionController;
use Application\Service\ListeDiffusion\ListeDiffusionService;
use Application\Service\ListeDiffusion\Url\UrlService;
use Application\Service\Role\RoleService;
use Fichier\Service\Fichier\FichierStorageService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Notification\Service\NotifierService;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;

class ListeDiffusionControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ListeDiffusionController
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
        $controller->setNotifierService($notifierService);

        /**
         * @var RoleService $roleService
         */
        $roleService = $container->get(RoleService::class);
        $controller->setApplicationRoleService($roleService);

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

        /**
         * @var \Application\Service\ListeDiffusion\Url\UrlService $urlService
         */
        $urlService = $container->get(UrlService::class);
        $controller->setUrlService($urlService);

        return $controller;
    }
}