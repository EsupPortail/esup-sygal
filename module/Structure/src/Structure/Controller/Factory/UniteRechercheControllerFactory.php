<?php

namespace Structure\Controller\Factory;

use Structure\Controller\UniteRechercheController;
use Structure\Form\UniteRechercheForm;
use Application\Service\CoEncadrant\CoEncadrantService;
use Application\Service\DomaineScientifiqueService;
use Structure\Service\Etablissement\EtablissementServiceLocateTrait;
use Individu\Service\Individu\IndividuService;
use Application\Service\Role\RoleService;
use Structure\Service\Structure\StructureService;
use Structure\Service\StructureDocument\StructureDocumentService;
use Structure\Service\UniteRecherche\UniteRechercheService;
use Interop\Container\ContainerInterface;

class UniteRechercheControllerFactory
{
    use EtablissementServiceLocateTrait;

    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return UniteRechercheController
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var UniteRechercheForm $form */
        $form = $container->get('FormElementManager')->get('UniteRechercheForm');

        /**
         * @var CoEncadrantService $coEncadrantService
         * @var UniteRechercheService $uniteRechercheService
         * @var IndividuService $individuService
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
        $controller->setRoleService($roleService);
        $controller->setEtablissementService($this->locateEtablissementService($container));
        $controller->setDomaineScientifiqueService($domaineService);
        $controller->setStructureService($structureService);
        $controller->setStructureDocumentService($structureDocumentService);
        $controller->setStructureForm($form);

        return $controller;
    }
}