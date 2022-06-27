<?php

namespace Structure\Controller\Factory;

use Structure\Controller\EtablissementController;
use Structure\Form\EtablissementForm;
use Structure\Service\Etablissement\EtablissementService;
use Individu\Service\IndividuService;
use Application\Service\Role\RoleService;
use Structure\Service\Structure\StructureService;
use Structure\Service\StructureDocument\StructureDocumentService;
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
         * @var IndividuService $individuService
         * @var RoleService $roleService
         * @var StructureService $structureService
         * @var StructureDocumentService $structureDocumentService
         */
        $etablissmentService = $container->get('EtablissementService');
        $roleService = $container->get('RoleService');
        $structureService = $container->get(StructureService::class);
        $structureDocumentService = $container->get(StructureDocumentService::class);

        $controller = new EtablissementController();
        $controller->setEtablissementService($etablissmentService);
        $controller->setRoleService($roleService);
        $controller->setStructureService($structureService);
        $controller->setStructureDocumentService($structureDocumentService);
        $controller->setStructureForm($form);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $controller->setSourceCodeStringHelper($sourceCodeHelper);

        return $controller;
    }
}