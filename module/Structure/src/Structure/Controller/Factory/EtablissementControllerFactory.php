<?php

namespace Structure\Controller\Factory;

use Application\Service\Role\RoleService;
use Application\Service\Variable\VariableService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Structure\Controller\EtablissementController;
use Structure\Form\EtablissementForm;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use Structure\Service\StructureDocument\StructureDocumentService;

class EtablissementControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EtablissementController
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
        $controller->setApplicationRoleService($roleService);
        $controller->setStructureService($structureService);
        $controller->setStructureDocumentService($structureDocumentService);
        $controller->setStructureForm($form);

        $variableService = $container->get(VariableService::class);
        $controller->setVariableService($variableService);

        return $controller;
    }
}