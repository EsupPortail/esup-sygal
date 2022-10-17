<?php

namespace Structure\Controller\Factory;

use These\Service\CoEncadrant\CoEncadrantService;
use Application\Service\Role\RoleService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;
use Structure\Controller\EcoleDoctoraleController;
use Structure\Form\EcoleDoctoraleForm;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleService;
use Structure\Service\Etablissement\EtablissementServiceLocateTrait;
use Structure\Service\Structure\StructureService;
use Structure\Service\StructureDocument\StructureDocumentService;

class EcoleDoctoraleControllerFactory
{
    use EtablissementServiceLocateTrait;

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EcoleDoctoraleController
    {
        /** @var EcoleDoctoraleForm $form */
        $form = $container->get('FormElementManager')->get('EcoleDoctoraleForm');

        /**
         * @var CoEncadrantService $coEncadrantService
         * @var EcoleDoctoraleService $ecoleDoctoralService
         * @var IndividuService $individuService
         * @var RoleService $roleService
         * @var StructureService $structureService
         * @var StructureDocumentService $structureDocumentService
         */
        $coEncadrantService = $container->get(CoEncadrantService::class);
        $ecoleDoctoralService = $container->get('EcoleDoctoraleService');
        $structureService = $container->get(StructureService::class);
        $roleService = $container->get('RoleService');
        $structureDocumentService = $container->get(StructureDocumentService::class);

        $controller = new EcoleDoctoraleController();
        $controller->setCoEncadrantService($coEncadrantService);
        $controller->setEcoleDoctoraleService($ecoleDoctoralService);
        $controller->setRoleService($roleService);
        $controller->setStructureForm($form);
        $controller->setStructureService($structureService);
        $controller->setStructureDocumentService($structureDocumentService);

        return $controller;
    }
}