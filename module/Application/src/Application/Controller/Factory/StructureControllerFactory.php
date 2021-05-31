<?php

namespace Application\Controller\Factory;

use Application\Controller\StructureController;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Fichier\FichierService;
use Application\Service\Individu\IndividuService;
use Application\Service\NatureFichier\NatureFichierService;
use Application\Service\Role\RoleService;
use Application\Service\Structure\StructureService;
use Application\Service\StructureDocument\StructureDocumentService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Interop\Container\ContainerInterface;

class StructureControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return StructureController
     */
    public function __invoke(ContainerInterface $container)
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

        $individuService = $container->get('IndividuService');
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
        $controller->setRoleService($roleService);
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