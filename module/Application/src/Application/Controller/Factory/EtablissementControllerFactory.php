<?php

namespace Application\Controller\Factory;

use Application\Controller\EtablissementController;
use Application\Form\EtablissementForm;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Fichier\FichierService;
use Application\Service\Individu\IndividuService;
use Application\Service\NatureFichier\NatureFichierService;
use Application\Service\Role\RoleService;
use Application\Service\Structure\StructureService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;

class EtablissementControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return EtablissementController
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var EtablissementForm $form */
        $form = $container->get('FormElementManager')->get('EtablissementForm');

        /**
         * @var EtablissementService $etablissmentService
         * @var FichierService $fichierService
         * @var IndividuService $individuService
         * @var RoleService $roleService
         * @var StructureService $structureService
         */
        $etablissmentService = $container->get('EtablissementService');
        $fichierService = $container->get(FichierService::class);
        $natureService = $container->get(NatureFichierService::class);
        $roleService = $container->get('RoleService');
        $structureService = $container->get(StructureService::class);

        $controller = new EtablissementController();
        $controller->setEtablissementService($etablissmentService);
        $controller->setFichierService($fichierService);
        $controller->setNatureFichierService($natureService);
        $controller->setRoleService($roleService);
        $controller->setStructureService($structureService);
        $controller->setStructureForm($form);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $controller->setSourceCodeStringHelper($sourceCodeHelper);

        return $controller;
    }
}