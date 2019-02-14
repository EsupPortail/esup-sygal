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
use Zend\Mvc\Controller\ControllerManager;

class UniteRechercheControllerFactory
{
    use EtablissementServiceLocateTrait;

    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return UniteRechercheController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        /** @var UniteRechercheForm $form */
        $form = $sl->get('FormElementManager')->get('UniteRechercheForm');

        /**
         * @var UniteRechercheService $uniteRechercheService
         * @var IndividuService $individuService
         * @var RoleService $roleService
         * @var StructureService $structureService
         * @var DomaineScientifiqueService $domaineService
         */
        $uniteRechercheService = $sl->get('UniteRechercheService');
        $roleService = $sl->get('RoleService');
        $structureService = $sl->get(StructureService::class);
        $domaineService = $sl->get(DomaineScientifiqueService::class);

        $controller = new UniteRechercheController();
        $controller->setUniteRechercheService($uniteRechercheService);
        $controller->setRoleService($roleService);
        $controller->setEtablissementService($this->locateEtablissementService($sl));
        $controller->setDomaineScientifiqueService($domaineService);
        $controller->setStructureService($structureService);
        $controller->setStructureForm($form);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $sl->get(SourceCodeStringHelper::class);
        $controller->setSourceCodeStringHelper($sourceCodeHelper);

        return $controller;
    }
}