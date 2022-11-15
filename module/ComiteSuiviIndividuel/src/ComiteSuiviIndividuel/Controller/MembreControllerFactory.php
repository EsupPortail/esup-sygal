<?php

namespace ComiteSuiviIndividuel\Controller;

use ComiteSuiviIndividuel\Form\Membre\MembreForm;
use ComiteSuiviIndividuel\Service\Membre\MembreService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class MembreControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return MembreController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : MembreController
    {
        /**
         * @var MembreService $membreService
         * @var MembreForm $membreForm
         */
        $membreService = $container->get(MembreService::class);
        $membreForm = $container->get('FormElementManager')->get(MembreForm::class);

        $controller = new MembreController();
        $controller->setMembreService($membreService);
        $controller->setMembreForm($membreForm);
        return $controller;
    }
}