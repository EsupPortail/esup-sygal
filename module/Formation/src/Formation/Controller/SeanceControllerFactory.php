<?php

namespace Formation\Controller;

use Formation\Service\Session\SessionService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Etablissement\EtablissementService;
use Application\Service\File\FileService;
use Doctrine\ORM\EntityManager;
use Formation\Form\Seance\SeanceForm;
use Formation\Service\Seance\SeanceService;
use Interop\Container\ContainerInterface;
use Laminas\View\Renderer\PhpRenderer;

class SeanceControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return SeanceController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : SeanceController
    {
        /**
         * @var EntityManager $entityManager
         * @var EtablissementService $etablissementService
         * @var FileService $fileService
         * @var SeanceService $seanceService
         * @var SessionService $sessionService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $etablissementService = $container->get(EtablissementService::class);
        $fileService = $container->get(FileService::class);
        $seanceService = $container->get(SeanceService::class);
        $sessionService = $container->get(SessionService::class);

        /**
         * @var SeanceForm $seanceForm
         */
        $seanceForm = $container->get('FormElementManager')->get(SeanceForm::class);

        /* @var $renderer PhpRenderer */
        $renderer = $container->get('ViewRenderer');

        $controller = new SeanceController();
        /** services **************************************************************************************************/
        $controller->setEntityManager($entityManager);
        $controller->setEtablissementService($etablissementService);
        $controller->setFileService($fileService);
        $controller->setSeanceService($seanceService);
        $controller->setSessionService($sessionService);
        /** forms *****************************************************************************************************/
        $controller->setSeanceForm($seanceForm);
        /** autre *****************************************************************************************************/
        $controller->setRenderer($renderer);

        return $controller;
    }
}