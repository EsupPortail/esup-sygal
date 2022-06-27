<?php

namespace Individu\Controller;

use Individu\Form\IndividuCompl\IndividuComplForm;
use Individu\Service\IndividuCompl\IndividuComplService;
use Individu\Service\IndividuService;
use Psr\Container\ContainerInterface;

class IndividuComplControllerFactory {

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : IndividuComplController
    {
        /**
         * @var \Individu\Service\IndividuService $individuService
         * @var \Individu\Service\IndividuCompl\IndividuComplService $individuComplService
         * @var \Individu\Form\IndividuCompl\IndividuComplForm $individuComplForm
         */
        $individuService = $container->get(IndividuService::class);
        $individuComplService = $container->get(IndividuComplService::class);
        $individuComplForm = $container->get('FormElementManager')->get(IndividuComplForm::class);

        $controller = new IndividuComplController();
        $controller->setIndividuService($individuService);
        $controller->setIndividuComplService($individuComplService);
        $controller->setIndividuComplForm($individuComplForm);

        return $controller;
    }
}