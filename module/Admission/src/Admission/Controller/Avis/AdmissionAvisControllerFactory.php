<?php

namespace Admission\Controller\Avis;

use Admission\Service\Etudiant\EtudiantService;
use Validation\Service\ValidationThese\ValidationTheseService;
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

        $controller = new AdmissionAvisController();
        $controller->setAdmissionService($admissionService);
        $controller->setAdmissionAvisService($admissionAvisService);

        $controller->setForm($avisForm);

        /** @var AvisService $rapportActiviteAvisRule */
        $avisService = $container->get(AvisService::class);
        $controller->setAvisService($avisService);

        return $controller;
    }
}