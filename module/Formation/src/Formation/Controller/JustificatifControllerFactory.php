<?php

namespace Formation\Controller;

use Doctrine\ORM\EntityManager;
use Fichier\Service\Fichier\FichierService;
use Fichier\Service\Fichier\FichierStorageService;
use Fichier\Service\NatureFichier\NatureFichierService;
use Formation\Service\Inscription\InscriptionService;
use Formation\Service\Justificatif\JustificatifService;
use Formation\Service\JustificatifSeance\JustificatifSeanceService;
use Formation\Service\Presence\PresenceService;
use Formation\Service\Seance\SeanceService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class JustificatifControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return JustificatifController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : JustificatifController
    {
        /**
         * @var EntityManager $entityManager
         * @var InscriptionService $inscriptionService
         * @var PresenceService $presenceService
         * @var SeanceService $seanceService
         * @var NatureFichierService $natureFichierService
         * @var FichierService $fichierService
         * @var JustificatifService $justificatifService
         * @var JustificatifSeanceService $justificatifSeanceService
         * @var FichierStorageService $fichierStorageService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $inscriptionService = $container->get(InscriptionService::class);
        $presenceService = $container->get(PresenceService::class);
        $seanceService = $container->get(SeanceService::class);
        $natureFichierService = $container->get(NatureFichierService::class);
        $fichierService = $container->get(FichierService::class);
        $justificatifService = $container->get(JustificatifService::class);
        $justificatifSeanceService = $container->get(JustificatifSeanceService::class);
        $fichierStorageService = $container->get(FichierStorageService::class);

        $controller = new JustificatifController();
        $controller->setEntityManager($entityManager);
        /** Service ***************************************************************************************************/
        $controller->setInscriptionService($inscriptionService);
        $controller->setPresenceService($presenceService);
        $controller->setSeanceService($seanceService);
        $controller->setNatureFichierService($natureFichierService);
        $controller->setFichierService($fichierService);
        $controller->setJustificatifService($justificatifService);
        $controller->setJustificatifSeanceService($justificatifSeanceService);
        $controller->setFichierStorageService($fichierStorageService);

        return $controller;
    }
}