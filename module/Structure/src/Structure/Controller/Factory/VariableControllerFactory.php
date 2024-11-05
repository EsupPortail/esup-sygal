<?php

namespace Structure\Controller\Factory;

use Application\Service\Role\RoleService;
use Application\Service\Variable\VariableService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Structure\Controller\VariableController;
use Structure\Form\VariableForm;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\StructureDocument\StructureDocumentService;

class VariableControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): VariableController
    {
        /** @var VariableForm $form */
        $form = $container->get('FormElementManager')->get(VariableForm::class);

        /**
         * @var EtablissementService $etablissmentService
         * @var IndividuService $individuService
         * @var RoleService $roleService
         * @var StructureDocumentService $structureDocumentService
         */
        $etablissmentService = $container->get('EtablissementService');

        $controller = new VariableController();
        $controller->setEtablissementService($etablissmentService);
        $controller->setVariableForm($form);

        $variableService = $container->get(VariableService::class);
        $controller->setVariableService($variableService);

        return $controller;
    }
}