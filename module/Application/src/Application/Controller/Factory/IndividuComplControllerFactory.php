<?php

namespace Application\Controller\Factory;

use Application\Controller\IndividuComplController;
use Application\Form\IndividuCompl\IndividuComplForm;
use Application\Service\IndividuCompl\IndividuComplService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class IndividuComplControllerFactory {

    public function __invoke(ContainerInterface $container) : IndividuComplController
    {
        /**
         * @var EntityManager $entityManager
         * @var IndividuComplService $individuComplService
         * @var IndividuComplForm $individuComplForm
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $individuComplService = $container->get(IndividuComplService::class);
        $individuComplForm = $container->get('FormElementManager')->get(IndividuComplForm::class);

        $controller = new IndividuComplController();
        $controller->setEntityManager($entityManager);
        $controller->setIndividuComplService($individuComplService);
        $controller->setIndividuComplForm($individuComplForm);
        return $controller;
    }
}