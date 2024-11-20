<?php

namespace Admission\Service\Admission;

use Admission\Service\Avis\AdmissionAvisService;
use Admission\Service\ConventionFormationDoctorale\ConventionFormationDoctoraleService;
use Admission\Service\Document\DocumentService;
use Admission\Service\Financement\FinancementService;
use Admission\Service\Etudiant\EtudiantService;
use Admission\Service\Inscription\InscriptionService;
use Admission\Service\Validation\AdmissionValidationService;
use Admission\Service\Verification\VerificationService;
use Application\Service\Variable\VariableService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnicaenRenderer\Service\Template\TemplateService;

class AdmissionServiceFactory {

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdmissionService
    {
        $etudiantService = $container->get(EtudiantService::class);
        $inscriptionService = $container->get(InscriptionService::class);
        $financementService = $container->get(FinancementService::class);
        $admissionValidationService = $container->get(AdmissionValidationService::class);
        $admissionAvisService = $container->get(AdmissionAvisService::class);
        $documentService = $container->get(DocumentService::class);
        $variableService = $container->get(VariableService::class);
        $conventionFormationDoctoraleService = $container->get(ConventionFormationDoctoraleService::class);
        $verificationService = $container->get(VerificationService::class);
        $templateService = $container->get(TemplateService::class);

        $service = new AdmissionService();
        $service->setEtudiantService($etudiantService);
        $service->setInscriptionService($inscriptionService);
        $service->setFinancementService($financementService);
        $service->setAdmissionValidationService($admissionValidationService);
        $service->setAdmissionAvisService($admissionAvisService);
        $service->setDocumentService($documentService);
        $service->setVariableService($variableService);
        $service->setConventionFormationDoctoraleService($conventionFormationDoctoraleService);
        $service->setVerificationService($verificationService);
        $service->setTemplateService($templateService);

        return $service;
    }
}