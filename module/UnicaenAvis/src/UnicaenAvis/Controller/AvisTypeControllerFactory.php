<?php

namespace UnicaenAvis\Controller;

use Psr\Container\ContainerInterface;
use UnicaenAvis\Form\AvisTypeForm;
use UnicaenAvis\Service\AvisService;

class AvisTypeControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AvisTypeController
    {
        $controller = new AvisTypeController();

        /** @var \UnicaenAvis\Form\AvisTypeForm $avisTypeForm */
        $avisTypeForm = $container->get('FormElementManager')->get(AvisTypeForm::class);
        $controller->setAvisTypeForm($avisTypeForm);

        /** @var \UnicaenAvis\Service\AvisService $rapportActiviteAvisRule */
        $avisService = $container->get(AvisService::class);
        $controller->setAvisService($avisService);

        return $controller;
    }
}