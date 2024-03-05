<?php

namespace Admission\Controller;

use Admission\Rule\Operation\AdmissionOperationRule;
use Admission\Service\Admission\AdmissionRechercheService;
use Admission\Service\Admission\AdmissionService;
use Admission\Service\Operation\AdmissionOperationService;
use Application\Service\Role\RoleService;
use Interop\Container\ContainerInterface;

class AdmissionRechercheControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return AdmissionRechercheController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var AdmissionService $admissionService
         * @var AdmissionRechercheService $admissionRechercheService
         * @var RoleService $roleService
         */
        $admissionService = $container->get(AdmissionService::class);
        $admissionRechercheService = $container->get(AdmissionRechercheService::class);
        $roleService = $container->get('RoleService');
        $admissionOperationService = $container->get(AdmissionOperationService::class);
        $admissionOperationRule = $container->get(AdmissionOperationRule::class);

        $controller = new AdmissionRechercheController();
        $controller->setSearchService($admissionRechercheService);
        $controller->setAdmissionService($admissionService);
        $controller->setAdmissionOperationService($admissionOperationService);
        $controller->setAdmissionOperationService($admissionOperationService);
        $controller->setAdmissionOperationRule($admissionOperationRule);
        $controller->setRoleService($roleService);

        return $controller;
    }
}