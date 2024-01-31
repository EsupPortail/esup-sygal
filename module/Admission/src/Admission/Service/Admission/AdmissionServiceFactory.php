<?php

namespace Admission\Service\Admission;

use Admission\Service\Avis\AdmissionAvisService;
use Admission\Service\Document\DocumentService;
use Admission\Service\Financement\FinancementService;
use Admission\Service\Etudiant\EtudiantService;
use Admission\Service\Inscription\InscriptionService;
use Admission\Service\Validation\AdmissionValidationService;
use Application\Service\Variable\VariableService;
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
        $etudiantService = $container->get(EtudiantService::class);
        $inscriptionService = $container->get(InscriptionService::class);
        $financementService = $container->get(FinancementService::class);
        $admissionValidationService = $container->get(AdmissionValidationService::class);
        $admissionAvisService = $container->get(AdmissionAvisService::class);
        $documentService = $container->get(DocumentService::class);
        $variableService = $container->get(VariableService::class);

        $service = new AdmissionService();
        $service->setEtudiantService($etudiantService);
        $service->setInscriptionService($inscriptionService);
        $service->setFinancementService($financementService);
        $service->setAdmissionValidationService($admissionValidationService);
        $service->setAdmissionAvisService($admissionAvisService);
        $service->setDocumentService($documentService);
        $service->setVariableService($variableService);

        return $service;
    }
}