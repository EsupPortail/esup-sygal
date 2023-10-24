<?php

namespace Admission\Service\Admission;

use Admission\Service\Document\DocumentService;
use Admission\Service\Financement\FinancementService;
use Admission\Service\Individu\IndividuService;
use Admission\Service\Inscription\InscriptionService;
use Admission\Service\Validation\ValidationService;
use Application\Service\Role\RoleService;
use Application\Service\Source\SourceService;
use Application\Service\UserContextService;
use Application\SourceCodeStringHelper;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class AdmissionServiceFactory {

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdmissionService
    {
        /**
         * @var RoleService $roleService
         * @var SourceService $sourceService
         * @var UserContextService $userContextService;
         */
        $roleService = $container->get(RoleService::class);
        $sourceService = $container->get(SourceService::class);
        $individuService = $container->get(IndividuService::class);
        $inscriptionService = $container->get(InscriptionService::class);
        $financementService = $container->get(FinancementService::class);
        $validationService = $container->get(ValidationService::class);
        $documentService = $container->get(DocumentService::class);
        $userContextService = $container->get('UserContextService');

        /**
         * @var SourceCodeStringHelper $sourceCodeStringHelper;
         */
        $sourceCodeStringHelper = $container->get(SourceCodeStringHelper::class);

        $service = new AdmissionService()   ;
        $service->setRoleService($roleService);
        $service->setSourceService($sourceService);
        $service->setIndividuAdmissionService($individuService);
        $service->setInscriptionService($inscriptionService);
        $service->setFinancementService($financementService);
        $service->setValidationService($validationService);
        $service->setDocumentService($documentService);
        $service->setUserContextService($userContextService);
        $service->setSourceCodeStringHelper($sourceCodeStringHelper);
        return $service;
    }
}