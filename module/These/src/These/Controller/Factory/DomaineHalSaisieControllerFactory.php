<?php

namespace These\Controller\Factory;

use Application\Service\DomaineHal\DomaineHalService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use These\Controller\DomaineHalSaisieController;
use These\Form\DomaineHalSaisie\DomaineHalSaisieForm;
use These\Form\TheseSaisie\TheseSaisieForm;
use These\Service\These\TheseService;

class DomaineHalSaisieControllerFactory
{

    /**
     * @param ContainerInterface $container
     * @return DomaineHalSaisieController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): DomaineHalSaisieController
    {
        /**
         * @var TheseService $theseService
         * @var TheseSaisieForm $theseSaisieForm
         */
        $theseService = $container->get(TheseService::class);
        $domaineHalService = $container->get(DomaineHalService::class);

        $domaineHalSaisieForm = $container->get('FormElementManager')->get(DomaineHalSaisieForm::class);


        $controller = new DomaineHalSaisieController();
        $controller->setTheseService($theseService);
        $controller->setDomaineHalService($domaineHalService);
        $controller->setDomaineHalSaisieForm($domaineHalSaisieForm);

        return $controller;
    }
}