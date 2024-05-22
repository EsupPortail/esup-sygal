<?php

namespace Admission\Controller\Avis;

use Admission\Service\Etudiant\EtudiantService;
use Application\Service\Validation\ValidationService;
use Psr\Container\ContainerInterface;
use Admission\Service\Avis\AdmissionAvisService;
use Admission\Service\Admission\AdmissionService;
use Admission\Service\Validation\AdmissionValidationService;
use UnicaenAvis\Form\AvisForm;
use UnicaenAvis\Service\AvisService;

class AdmissionAvisControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdmissionAvisController
    {
        $admissionService = $container->get(AdmissionService::class);
        $admissionAvisService = $container->get(AdmissionAvisService::class);
        $avisForm = $container->get('FormElementManager')->get(AvisForm::class);
        $admissionValidationService = $container->get(AdmissionValidationService::class);
        $validationService = $container->get(ValidationService::class);
        $etudiantService = $container->get(EtudiantService::class);

        $controller = new AdmissionAvisController();
        $controller->setAdmissionService($admissionService);
        $controller->setAdmissionAvisService($admissionAvisService);
        $controller->setAdmissionValidationService($admissionValidationService);
        $controller->setValidationService($validationService);
        $controller->setEtudiantService($etudiantService);

        $controller->setForm($avisForm);

        /** @var \UnicaenAvis\Service\AvisService $rapportActiviteAvisRule */
        $avisService = $container->get(AvisService::class);
        $controller->setAvisService($avisService);

        return $controller;
    }
}