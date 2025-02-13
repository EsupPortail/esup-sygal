<?php

namespace Soutenance\Controller;

use Acteur\Service\ActeurHDR\ActeurHDRService;
use Acteur\Service\ActeurThese\ActeurTheseService;
use Depot\Service\FichierHDR\FichierHDRService;
use Depot\Service\FichierThese\FichierTheseService;
use HDR\Service\HDRService;
use Interop\Container\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Form\Justificatif\JustificatifForm;
use Soutenance\Service\Justificatif\JustificatifService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRService;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseService;
use These\Service\These\TheseService;
use UnicaenParametre\Service\Parametre\ParametreService;

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
         * @var FichierTheseService $fichierTheseService
         * @var FichierHDRService $fichierHDRService
         * @var JustificatifService $justificatifService
         * @var MembreService $membreService
         * @var ParametreService $parametreService
         * @var PropositionTheseService $propositionTheseService
         * @var PropositionHDRService $propositionHDRService
         */
        $fichierTheseService = $container->get(FichierTheseService::class);
        $fichierHDRService = $container->get(FichierHDRService::class);
        $justificatifService = $container->get(JustificatifService::class);
        $membreService = $container->get(MembreService::class);
        $parametreService = $container->get(ParametreService::class);
        $propositionTheseService = $container->get(PropositionTheseService::class);
        $propositionHDRService = $container->get(PropositionHDRService::class);

        /**
         * @var JustificatifForm $justificatifForm
         */
        $justificatifForm = $container->get('FormElementManager')->get(JustificatifForm::class);

        $controller = new JustificatifController();
        $controller->setFichierTheseService($fichierTheseService);
        $controller->setFichierHDRService($fichierHDRService);
        $controller->setJustificatifService($justificatifService);
        $controller->setMembreService($membreService);
        $controller->setParametreService($parametreService);
        $controller->setJustificatifForm($justificatifForm);
        $controller->setPropositionTheseService($propositionTheseService);
        $controller->setPropositionHDRService($propositionHDRService);

        return $controller;
    }
}