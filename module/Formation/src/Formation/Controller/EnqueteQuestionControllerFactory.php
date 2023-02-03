<?php

namespace Formation\Controller;

use Doctrine\ORM\EntityManager;
use Formation\Form\EnqueteCategorie\EnqueteCategorieForm;
use Formation\Form\EnqueteQuestion\EnqueteQuestionForm;
use Formation\Form\EnqueteReponse\EnqueteReponseForm;
use Formation\Service\EnqueteCategorie\EnqueteCategorieService;
use Formation\Service\EnqueteQuestion\EnqueteQuestionService;
use Formation\Service\EnqueteReponse\EnqueteReponseService;
use Formation\Service\Inscription\InscriptionService;
use Formation\Service\Session\SessionService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use UnicaenParametre\Service\Parametre\ParametreService;

class EnqueteQuestionControllerFactory {

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : EnqueteQuestionController
    {
        /**
         * @var EntityManager $entityManager
         * @var EnqueteCategorieService $enqueteCategorieService
         * @var EnqueteReponseService $enqueteReponseService
         * @var InscriptionService $inscriptionService
         * @var ParametreService $parametreService
         * @var SessionService $sessionService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $enqueteCategorieService = $container->get(EnqueteCategorieService::class);
        $enqueteQuestionService = $container->get(EnqueteQuestionService::class);
        $enqueteReponseService = $container->get(EnqueteReponseService::class);
        $inscriptionService = $container->get(InscriptionService::class);
        $parametreService = $container->get(ParametreService::class);
        $sessionService = $container->get(SessionService::class);

        /**
         * @var EnqueteCategorieForm $enqueteCategorieForm
         * @var EnqueteQuestionForm $enqueteQuestionForm
         * @var EnqueteReponseForm $enqueteReponseForm
         */
        $enqueteCategorieForm = $container->get('FormElementManager')->get(EnqueteCategorieForm::class);
        $enqueteQuestionForm = $container->get('FormElementManager')->get(EnqueteQuestionForm::class);
        $enqueteReponseForm = $container->get('FormElementManager')->get(EnqueteReponseForm::class);

        $controller = new EnqueteQuestionController();
        $controller->setEntityManager($entityManager);
        $controller->setEnqueteCategorieService($enqueteCategorieService);
        $controller->setEnqueteQuestionService($enqueteQuestionService);
        $controller->setEnqueteReponseService($enqueteReponseService);
        $controller->setInscriptionService($inscriptionService);
        $controller->setParametreService($parametreService);
        $controller->setSessionService($sessionService);
        $controller->setEnqueteCategorieForm($enqueteCategorieForm);
        $controller->setEnqueteQuestionForm($enqueteQuestionForm);
        $controller->setEnqueteReponseForm($enqueteReponseForm);

        return $controller;
    }
}