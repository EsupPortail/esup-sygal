<?php

namespace InscriptionAdministrative\Controller;

use Admission\Service\Admission\AdmissionService;
use Application\Service\AnneeUniv\AnneeUnivService;
use Doctorant\Service\DoctorantService;
use Individu\Service\IndividuService;
use InscriptionAdministrative\Config\ModuleConfig;
use InscriptionAdministrative\Process\Reception\InscriptionAdministrativeReceptionProcess;
use InscriptionAdministrative\Rule\Reception\ReceptionRulePluginManager;
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

        /** @var \InscriptionAdministrative\Rule\Reception\ReceptionRulePluginManager $receptionRulePluginManager */
        $receptionRulePluginManager = $container->get(ReceptionRulePluginManager::class);
        $controller->setReceptionRulePluginManager($receptionRulePluginManager);

        /** @var \Application\Service\AnneeUniv\AnneeUnivService $anneeUnivService */
        $anneeUnivService = $container->get(AnneeUnivService::class);
        $controller->setanneeUnivService($anneeUnivService);

        /** @var AdmissionService $admissionService */
        $admissionService = $container->get(AdmissionService::class);
        $controller->setAdmissionService($admissionService);

        /** @var IndividuService $individuService */
        $individuService = $container->get(IndividuService::class);
        $controller->setIndividuService($individuService);

        /** @var DoctorantService $doctorantService */
        $doctorantService = $container->get(DoctorantService::class);
        $controller->setDoctorantService($doctorantService);

        /** @var InscriptionAdministrativeReceptionProcess $receptionProcess */
        $receptionProcess = $container->get(InscriptionAdministrativeReceptionProcess::class);
        $controller->setInscriptionAdministrativeReceptionProcess($receptionProcess);

        /** @var ModuleConfig $moduleConfig */
        $moduleConfig = $container->get(ModuleConfig::class);
        $controller->setModuleConfig($moduleConfig);

        return $controller;
    }
}