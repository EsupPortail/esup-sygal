<?php

namespace InscriptionAdministrative\Controller;

use Admission\Service\Admission\AdmissionService;
use Application\Service\AnneeUniv\AnneeUnivService;
use InscriptionAdministrative\Process\Reception\ReceptionProcess;
use InscriptionAdministrative\Rule\Reception\ReceptionRule;
use InscriptionAdministrative\Service\InscriptionAdministrativeService;
use Psr\Container\ContainerInterface;

class InscriptionAdministrativeControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): InscriptionAdministrativeController
    {
        $controller = new InscriptionAdministrativeController();

        /** @var \InscriptionAdministrative\Service\InscriptionAdministrativeService $inscriptionAdministrativeService */
        $inscriptionAdministrativeService = $container->get(InscriptionAdministrativeService::class);
        $controller->setInscriptionAdministrativeService($inscriptionAdministrativeService);

        /** @var ReceptionRule $receptionRule */
        $receptionRule = $container->get(ReceptionRule::class);
        $controller->setReceptionRule($receptionRule);

        /** @var \Application\Service\AnneeUniv\AnneeUnivService $anneeUnivService */
        $anneeUnivService = $container->get(AnneeUnivService::class);
        $controller->setanneeUnivService($anneeUnivService);

        /** @var AdmissionService $admissionService */
        $admissionService = $container->get(AdmissionService::class);
        $controller->setAdmissionService($admissionService);

        /** @var ReceptionProcess $receptionProcess */
        $receptionProcess = $container->get(ReceptionProcess::class);
        $controller->setReceptionProcess($receptionProcess);

        return $controller;
    }
}