<?php

namespace InscriptionAdministrative\Rule\Reception;

use Admission\Service\Admission\AdmissionService;
use Doctorant\Service\DoctorantService;
use Individu\Service\IndividuService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use These\Service\These\TheseService;
use These\Service\TheseAnneeUniv\TheseAnneeUnivService;

class ReceptionRuleFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ReceptionRule
    {
        $rule = new ReceptionRule();

        /** @var IndividuService $individuService */
        $individuService = $container->get(IndividuService::class);
        $rule->setIndividuService($individuService);

        /** @var DoctorantService $doctorantService */
        $doctorantService = $container->get(DoctorantService::class);
        $rule->setDoctorantService($doctorantService);

        /** @var TheseService $theseService */
        $theseService = $container->get(TheseService::class);
        $rule->setTheseService($theseService);

        /** @var TheseAnneeUnivService $theseAnneeUnivService */
        $theseAnneeUnivService = $container->get(TheseAnneeUnivService::class);
        $rule->setTheseAnneeUnivService($theseAnneeUnivService);

        /** @var AdmissionService $admissionService */
        $admissionService = $container->get(AdmissionService::class);
        $rule->setAdmissionService($admissionService);

        $rule->setDoctorantUpdateEnabled();

        return $rule;
    }
}