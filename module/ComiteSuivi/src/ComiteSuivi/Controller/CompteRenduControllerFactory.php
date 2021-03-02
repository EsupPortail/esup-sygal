<?php

namespace ComiteSuivi\Controller;

use ComiteSuivi\Form\CompteRendu\CompteRenduForm;
use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviService;
use ComiteSuivi\Service\CompteRendu\CompteRenduService;
use ComiteSuivi\Service\Membre\MembreService;
use Interop\Container\ContainerInterface;

class CompteRenduControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return CompteRenduController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var ComiteSuiviService $comiteSuiviService
         * @var CompteRenduService $compteRenduService
         * @var MembreService $membreService
         */
        $comiteSuiviService = $container->get(ComiteSuiviService::class);
        $compteRenduService = $container->get(CompteRenduService::class);
        $membreService = $container->get(MembreService::class);

        /**
         * @var CompteRenduForm $compteRenduForm
         */
        $compteRenduForm = $container->get('FormElementManager')->get(CompteRenduForm::class);

        $controller = new CompteRenduController();
        $controller->setComiteSuiviService($comiteSuiviService);
        $controller->setCompteRenduService($compteRenduService);
        $controller->setMembreService($membreService);
        $controller->setCompteRenduForm($compteRenduForm);
        return $controller;
    }

}