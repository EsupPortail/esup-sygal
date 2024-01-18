<?php

namespace Admission\Service\Admission;

use Admission\Service\Avis\AdmissionAvisService;
use Admission\Service\Document\DocumentService;
use Admission\Service\Financement\FinancementService;
use Admission\Service\Etudiant\EtudiantService;
use Admission\Service\Inscription\InscriptionService;
use Admission\Service\Validation\AdmissionValidationService;
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
        $etudiantService = $container->get(EtudiantService::class);
        $inscriptionService = $container->get(InscriptionService::class);
        $financementService = $container->get(FinancementService::class);
        $admissionValidationService = $container->get(AdmissionValidationService::class);
        $admissionAvisService = $container->get(AdmissionAvisService::class);
        $documentService = $container->get(DocumentService::class);
        $userContextService = $container->get('UserContextService');

        /**
         * @var SourceCodeStringHelper $sourceCodeStringHelper;
         */
        $sourceCodeStringHelper = $container->get(SourceCodeStringHelper::class);

        $service = new AdmissionService()   ;
        $service->setRoleService($roleService);
        $service->setSourceService($sourceService);
        $service->setEtudiantService($etudiantService);
        $service->setInscriptionService($inscriptionService);
        $service->setFinancementService($financementService);
        $service->setAdmissionValidationService($admissionValidationService);
        $service->setAdmissionAvisService($admissionAvisService);
        $service->setDocumentService($documentService);
        $service->setUserContextService($userContextService);
        $service->setSourceCodeStringHelper($sourceCodeStringHelper);
        return $service;
    }
}