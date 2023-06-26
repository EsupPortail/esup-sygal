<?php

namespace Doctorant\Controller;

use Doctorant\Form\MissionEnseignement\MissionEnseignementForm;
use Doctorant\Service\DoctorantService;
use Doctorant\Service\MissionEnseignement\MissionEnseignementService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class MissionEnseignementControllerFactory
{

    /**
     * @param ContainerInterface $container
     * @return MissionEnseignementController
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): MissionEnseignementController
    {
        /**
         * @var DoctorantService $doctorantService
         * @var MissionEnseignementService $missionEnseignementService
         * @var MissionEnseignementForm $missionEnseignementForm
         */
        $doctorantService = $container->get(DoctorantService::class);
        $missionEnseignementService = $container->get(MissionEnseignementService::class);
        $missionEnseignementForm = $container->get('FormElementManager')->get(MissionEnseignementForm::class);

        $controller = new MissionEnseignementController();
        $controller->setDoctorantService($doctorantService);
        $controller->setMissionEnseignementService($missionEnseignementService);
        $controller->setMissionEnseignementForm($missionEnseignementForm);
        return $controller;
    }

}