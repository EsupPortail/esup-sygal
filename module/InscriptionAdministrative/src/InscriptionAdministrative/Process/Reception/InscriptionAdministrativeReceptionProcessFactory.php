<?php

namespace InscriptionAdministrative\Process\Reception;

use Admission\Service\Admission\AdmissionService;
use Doctorant\Service\DoctorantService;
use Individu\Service\IndividuService;
use InscriptionAdministrative\Config\ModuleConfig;
use InscriptionAdministrative\Rule\Reception\ReceptionRulePluginManager;
use InscriptionAdministrative\Service\InscriptionAdministrativeService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use These\Service\These\TheseService;
use These\Service\TheseAnneeUniv\TheseAnneeUnivService;

class InscriptionAdministrativeReceptionProcessFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): InscriptionAdministrativeReceptionProcess
    {
        $service = new InscriptionAdministrativeReceptionProcess();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $service->setEntityManager($em);

        /** @var \InscriptionAdministrative\Rule\Reception\ReceptionRulePluginManager $receptionRulePluginManager */
        $receptionRulePluginManager = $container->get(ReceptionRulePluginManager::class);
        $service->setReceptionRulePluginManager($receptionRulePluginManager);

        /** @var InscriptionAdministrativeService $inscriptionAdministrativeService */
        $inscriptionAdministrativeService = $container->get(InscriptionAdministrativeService::class);
        $service->setInscriptionAdministrativeService($inscriptionAdministrativeService);

        /** @var \Individu\Service\IndividuService $individuService */
        $individuService = $container->get(IndividuService::class);
        $service->setIndividuService($individuService);

        /** @var DoctorantService $doctorantService */
        $doctorantService = $container->get(DoctorantService::class);
        $service->setDoctorantService($doctorantService);

        /** @var TheseService $theseService */
        $theseService = $container->get(TheseService::class);
        $service->setTheseService($theseService);

        /** @var TheseAnneeUnivService $theseAnneeUnivService */
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);
        $service->setTheseAnneeUnivService($theseAnneeUnivService);

        /** @var AdmissionService $admissionService */
        $admissionService = $container->get(AdmissionService::class);
        $service->setAdmissionService($admissionService);

        /** @var ModuleConfig $moduleConfig */
        $moduleConfig = $container->get(ModuleConfig::class);
        $service->setReceptionConfig($moduleConfig->getReception());

        return $service;
    }
}