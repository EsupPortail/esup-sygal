<?php

namespace Structure\Controller\Factory;

use Application\Service\DomaineScientifiqueService;
use Application\Service\Role\RoleService;
use Interop\Container\ContainerInterface;
use Structure\Controller\UniteRechercheController;
use Structure\Form\UniteRechercheForm;
use Structure\Service\Etablissement\EtablissementServiceLocateTrait;
use Structure\Service\Structure\StructureService;
use Structure\Service\StructureDocument\StructureDocumentService;
use Structure\Service\UniteRecherche\UniteRechercheService;
use These\Service\CoEncadrant\CoEncadrantService;

class UniteRechercheControllerFactory
{
    use EtablissementServiceLocateTrait;

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): UniteRechercheController
    {
        /** @var UniteRechercheForm $form */
        $form = $container->get('FormElementManager')->get('UniteRechercheForm');

        /**
         * @var CoEncadrantService $coEncadrantService
         * @var UniteRechercheService $uniteRechercheService
         * @var RoleService $roleService
         * @var StructureService $structureService
         * @var DomaineScientifiqueService $domaineService
         * @var StructureDocumentService $structureDocumentService
         */
        $coEncadrantService = $container->get(CoEncadrantService::class);
        $uniteRechercheService = $container->get('UniteRechercheService');
        $roleService = $container->get('RoleService');
        $structureService = $container->get(StructureService::class);
        $domaineService = $container->get(DomaineScientifiqueService::class);
        $structureDocumentService = $container->get(StructureDocumentService::class);

        $controller = new UniteRechercheController();
        $controller->setCoEncadrantService($coEncadrantService);
        $controller->setUniteRechercheService($uniteRechercheService);
        $controller->setApplicationRoleService($roleService);
        $controller->setEtablissementService($this->locateEtablissementService($container));
        $controller->setDomaineScientifiqueService($domaineService);
        $controller->setStructureService($structureService);
        $controller->setStructureDocumentService($structureDocumentService);
        $controller->setStructureForm($form);

        return $controller;
    }
}