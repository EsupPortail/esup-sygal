<?php

namespace Application\Controller\Factory;

use Application\Controller\RoleController;
use Application\Form\RoleForm;
use Application\Service\Role\RoleService;
use Application\Service\Source\SourceService;
use Interop\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;

class RoleControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return RoleController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var RoleService $roleService
         * @var EtablissementService $etablissementService
         */
        $roleService = $container->get('RoleService');
        $etablissementService = $container->get(EtablissementService::class);

        $theseSaisieForm = $container->get('FormElementManager')->get(RoleForm::class);
        $sourceService = $container->get(SourceService::class);

        $controller = new RoleController();
        $controller->setRoleService($roleService);
        $controller->setEtablissementService($etablissementService);
        $controller->setRoleForm($theseSaisieForm);
        $controller->setSource($sourceService->fetchApplicationSource());


        return $controller;
    }
}