<?php

namespace Application\Controller\Factory;

use Application\Controller\UniteRechercheController;
use Application\Form\UniteRechercheForm;
use Application\Service\DomaineScientifiqueService;
use Application\Service\Etablissement\EtablissementServiceLocateTrait;
use Application\Service\Individu\IndividuService;
use Application\Service\Role\RoleService;
use Application\Service\Structure\StructureService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Application\SourceCodeStringHelper;
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
         * @var UniteRechercheService $uniteRechercheService
         * @var IndividuService $individuService
         * @var RoleService $roleService
         * @var StructureService $structureService
         * @var DomaineScientifiqueService $domaineService
         */
        $uniteRechercheService = $container->get('UniteRechercheService');
        $roleService = $container->get('RoleService');
        $structureService = $container->get(StructureService::class);
        $domaineService = $container->get(DomaineScientifiqueService::class);

        $controller = new UniteRechercheController();
        $controller->setUniteRechercheService($uniteRechercheService);
        $controller->setRoleService($roleService);
        $controller->setEtablissementService($this->locateEtablissementService($container));
        $controller->setDomaineScientifiqueService($domaineService);
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