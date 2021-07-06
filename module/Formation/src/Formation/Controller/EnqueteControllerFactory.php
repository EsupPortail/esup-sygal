<?php

namespace Formation\Controller;

use Doctrine\ORM\EntityManager;
use Formation\Form\EnqueteReponse\EnqueteReponseForm;
use Interop\Container\ContainerInterface;

class EnqueteControllerFactory {

    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        /**
         * @var EnqueteReponseForm $enqueteReponseForm
         */
        $enqueteReponseForm = $container->get('FormElementManager')->get(EnqueteReponseForm::class);

        $controller = new EnqueteController();
        $controller->setEntityManager($entityManager);
        $controller->setEnqueteReponseForm($enqueteReponseForm);
        return $controller;
    }
}