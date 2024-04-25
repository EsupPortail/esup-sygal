<?php

namespace Formation\Controller;

use Application\Service\AnneeUniv\AnneeUnivService;
use Fichier\Service\Fichier\FichierStorageService;
use Formation\Service\Session\SessionService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Etablissement\EtablissementService;
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
         * @var \Fichier\Service\Fichier\FichierStorageService $fichierStorageService
         * @var SeanceService $seanceService
         * @var SessionService $sessionService
         * @var AnneeUnivService $anneeUnivService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $etablissementService = $container->get(EtablissementService::class);
        $fichierStorageService = $container->get(FichierStorageService::class);
        $seanceService = $container->get(SeanceService::class);
        $sessionService = $container->get(SessionService::class);
        $anneeUnivService = $container->get(AnneeUnivService::class);

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
        $controller->setFichierStorageService($fichierStorageService);
        $controller->setSeanceService($seanceService);
        $controller->setSessionService($sessionService);
        $controller->setAnneeUnivService($anneeUnivService);
        /** forms *****************************************************************************************************/
        $controller->setSeanceForm($seanceForm);
        /** autre *****************************************************************************************************/
        $controller->setRenderer($renderer);

        return $controller;
    }
}