<?php

namespace Application\Controller\Factory;

use Application\Controller\EtablissementController;
use Application\Form\EtablissementForm;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\File\FileService;
use Application\Service\Individu\IndividuService;
use Application\Service\Role\RoleService;
use Application\Service\Structure\StructureService;
use Zend\Mvc\Controller\ControllerManager;

class EtablissementControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return EtablissementController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        /** @var EtablissementForm $form */
        $form = $controllerManager->getServiceLocator()->get('FormElementManager')->get('EtablissementForm');

        /**
         * @var EtablissementService $etablissmentService
         * @var IndividuService $individuService
         * @var RoleService $roleService
         * @var StructureService $structureService
         */
        $etablissmentService = $sl->get('EtablissementService');
        $individuService = $sl->get('IndividuService');
        $roleService = $sl->get('RoleService');
        $structureService = $sl->get(StructureService::class);

        /**
         * @var FileService $fileService
         */
        $fileService = $sl->get(FileService::class);

        $controller = new EtablissementController();
        $controller->setEtablissementService($etablissmentService);
        $controller->setIndividuService($individuService);
        $controller->setRoleService($roleService);
        $controller->setStructureService($structureService);
        $controller->setEtablissementForm($form);
        $controller->setFileService($fileService);

        return $controller;
    }
}