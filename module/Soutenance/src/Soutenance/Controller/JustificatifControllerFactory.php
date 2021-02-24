<?php

namespace Soutenance\Controller;

use Application\Service\FichierThese\FichierTheseService;
use Interop\Container\ContainerInterface;
use Soutenance\Form\Justificatif\JustificatifForm;
use Soutenance\Service\Justificatif\JustificatifService;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Parametre\ParametreService;
use Soutenance\Service\Proposition\PropositionService;

class JustificatifControllerFactory {

    /**
     * @param ContainerInterface $container
     * @return JustificatifController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var FichierTheseService $fichierTheseService
         * @var JustificatifService $justificatifService
         * @var MembreService $membreService
         * @var ParametreService $parametreService
         * @var PropositionService $propositionService
         */
        $fichierTheseService = $container->get(FichierTheseService::class);
        $justificatifService = $container->get(JustificatifService::class);
        $membreService = $container->get(MembreService::class);
        $parametreService = $container->get(ParametreService::class);
        $propositionService = $container->get(PropositionService::class);

        /**
         * @var JustificatifForm $justificatifForm
         */
        $justificatifForm = $container->get('FormElementManager')->get(JustificatifForm::class);

        $controller = new JustificatifController();
        $controller->setFichierTheseService($fichierTheseService);
        $controller->setJustificatifService($justificatifService);
        $controller->setMembreService($membreService);
        $controller->setParametreService($parametreService);
        $controller->setPropositionService($propositionService);
        $controller->setJustificatifForm($justificatifForm);
        return $controller;
    }
}