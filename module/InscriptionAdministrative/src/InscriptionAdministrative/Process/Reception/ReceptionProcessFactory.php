<?php

namespace InscriptionAdministrative\Process\Reception;

use Admission\Service\Admission\AdmissionService;
use Doctorant\Service\DoctorantService;
use Individu\Service\IndividuService;
use InscriptionAdministrative\Rule\Reception\ReceptionRule;
use InscriptionAdministrative\Service\InscriptionAdministrativeService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use These\Service\These\TheseService;
use These\Service\TheseAnneeUniv\TheseAnneeUnivService;

class ReceptionProcessFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ReceptionProcess
    {
        $service = new ReceptionProcess();

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $container->get('doctrine.entitymanager.orm_default');
        $service->setEntityManager($em);

        /** @var ReceptionRule $receptionRule */
        $receptionRule = $container->get(ReceptionRule::class);
        $service->setReceptionRule($receptionRule);

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

        return $service;
    }
}