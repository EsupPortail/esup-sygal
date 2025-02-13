<?php

namespace RapportActivite\Controller\Avis;

use Validation\Service\ValidationThese\ValidationTheseService;
use Psr\Container\ContainerInterface;
use RapportActivite\Service\Avis\RapportActiviteAvisService;
use RapportActivite\Service\RapportActiviteService;
use RapportActivite\Service\Validation\RapportActiviteValidationService;
use UnicaenAvis\Form\AvisForm;
use UnicaenAvis\Service\AvisService;

class RapportActiviteAvisControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): RapportActiviteAvisController
    {
        $rapportService = $container->get(RapportActiviteService::class);
        $rapportAvisService = $container->get(RapportActiviteAvisService::class);
        $avisForm = $container->get('FormElementManager')->get(AvisForm::class);
        $rapportValidationService = $container->get(RapportActiviteValidationService::class);
        $validationService = $container->get(ValidationTheseService::class);

        $controller = new RapportActiviteAvisController();
        $controller->setRapportActiviteService($rapportService);
        $controller->setRapportActiviteAvisService($rapportAvisService);
        $controller->setRapportActiviteValidationService($rapportValidationService);
        $controller->setValidationTheseService($validationService);

        $controller->setForm($avisForm);

        /** @var \UnicaenAvis\Service\AvisService $rapportActiviteAvisRule */
        $avisService = $container->get(AvisService::class);
        $controller->setAvisService($avisService);

        return $controller;
    }
}