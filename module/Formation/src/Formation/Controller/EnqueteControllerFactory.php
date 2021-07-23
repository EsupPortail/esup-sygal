<?php

namespace Formation\Controller;

use Doctrine\ORM\EntityManager;
use Formation\Form\EnqueteQuestion\EnqueteQuestionForm;
use Formation\Form\EnqueteReponse\EnqueteReponseForm;
use Formation\Service\EnqueteQuestion\EnqueteQuestionService;
use Formation\Service\EnqueteReponse\EnqueteReponseService;
use Interop\Container\ContainerInterface;

class EnqueteControllerFactory {

    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var EntityManager $entityManager
         * @var EnqueteQuestionService $enqueteQuestionService
         * @var EnqueteReponseService $enqueteReponseService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $enqueteQuestionService = $container->get(EnqueteQuestionService::class);
        $enqueteReponseService = $container->get(EnqueteReponseService::class);

        /**
         * @var EnqueteQuestionForm $enqueteQuestionForm
         * @var EnqueteReponseForm $enqueteReponseForm
         */
        $enqueteQuestionForm = $container->get('FormElementManager')->get(EnqueteQuestionForm::class);
        $enqueteReponseForm = $container->get('FormElementManager')->get(EnqueteReponseForm::class);

        $controller = new EnqueteController();
        $controller->setEntityManager($entityManager);
        $controller->setEnqueteQuestionService($enqueteQuestionService);
        $controller->setEnqueteReponseService($enqueteReponseService);
        $controller->setEnqueteReponseForm($enqueteReponseForm);
        $controller->setEnqueteQuestionForm($enqueteQuestionForm);
        return $controller;
    }
}