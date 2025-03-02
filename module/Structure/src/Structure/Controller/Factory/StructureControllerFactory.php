<?php

namespace Structure\Controller\Factory;

use Application\Service\Role\RoleService;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\NatureFichier\NatureFichierService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Structure\Controller\StructureController;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use Structure\Service\StructureDocument\StructureDocumentService;
use Structure\Service\UniteRecherche\UniteRechercheService;

class StructureControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): StructureController
    {
        /**
         * @var IndividuService $individuService
         * @var RoleService $roleService
         * @var StructureService $structureService
         * @var EcoleDoctoraleService $ecoleService
         * @var UniteRechercheService $uniteService
         * @var EtablissementService $etablissementService
         * @var NatureFichierService $natureFichierService
         * @var FichierService $fichierService
         * @var StructureDocumentService $structureDocumentService
         */

        $individuService = $container->get(IndividuService::class);
        $roleService = $container->get('RoleService');
        $structureService = $container->get('StructureService');
        $ecoleService = $container->get('EcoleDoctoraleService');
        $uniteService = $container->get('UniteRechercheService');
        $etablissementService = $container->get('EtablissementService');
        $natureFichierService = $container->get('NatureFichierService');
        $fichierService = $container->get('FichierService');
        $structureDocumentService = $container->get(StructureDocumentService::class);

        $controller = new StructureController();
        $controller->setIndividuService($individuService);
        $controller->setApplicationRoleService($roleService);
        $controller->setStructureService($structureService);
        $controller->setEcoleDoctoraleService($ecoleService);
        $controller->setUniteRechercheService($uniteService);
        $controller->setEtablissementService($etablissementService);
        $controller->setNatureFichierService($natureFichierService);
        $controller->setFichierService($fichierService);
        $controller->setStructureDocumentService($structureDocumentService);

        return $controller;
    }
}