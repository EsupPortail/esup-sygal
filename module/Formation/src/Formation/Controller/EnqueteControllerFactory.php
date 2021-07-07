<?php

namespace Formation\Controller;

use Doctrine\ORM\EntityManager;
use Formation\Form\EnqueteReponse\EnqueteReponseForm;
use Formation\Service\EnqueteReponse\EnqueteReponseService;
use Interop\Container\ContainerInterface;

class EnqueteControllerFactory {

    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var EnqueteReponseService $enqueteReponseService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $enqueteReponseService = $container->get(EnqueteReponseService::class);

        /**
         * @var EnqueteReponseForm $enqueteReponseForm
         */
        $enqueteReponseForm = $container->get('FormElementManager')->get(EnqueteReponseForm::class);

        $controller = new EnqueteController();
        $controller->setEntityManager($entityManager);
        $controller->setEnqueteReponseService($enqueteReponseService);
        $controller->setEnqueteReponseForm($enqueteReponseForm);
        return $controller;
    }
}