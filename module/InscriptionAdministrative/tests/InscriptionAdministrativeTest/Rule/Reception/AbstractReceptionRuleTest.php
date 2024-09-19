<?php

namespace InscriptionAdministrativeTest\Rule\Reception;

use Admission\Entity\Db\Repository\AdmissionRepository;
use Admission\Service\Admission\AdmissionService;
use Doctorant\Entity\Db\Repository\DoctorantRepository;
use Doctorant\Service\DoctorantService;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use InscriptionAdministrative\Rule\Reception\ReceptionRule;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use These\Entity\Db\Repository\TheseRepository;
use These\Service\These\TheseService;

abstract class AbstractReceptionRuleTest extends TestCase
{
    protected DoctorantService|Stub $doctorantService;
    protected TheseService|Stub $theseService;
    protected AdmissionService|Stub $admissionService;

    protected DoctorantRepository|Stub $doctorantRepository;
    protected TheseRepository|Stub $theseRepository;
    protected AdmissionRepository|Stub $admissionRepository;

    protected InscriptionAdministrative|MockObject $ia;


    protected function setUp(): void
    {
        $this->doctorantRepository = $this->createStub(DoctorantRepository::class);
        $this->doctorantService = $this->createStub(DoctorantService::class);
        $this->doctorantService->method('getRepository')->willReturn($this->doctorantRepository);

        $this->theseRepository = $this->createStub(TheseRepository::class);
        $this->theseService = $this->createStub(TheseService::class);
        $this->theseService->method('getRepository')->willReturn($this->theseRepository);

        $this->admissionRepository = $this->createStub(AdmissionRepository::class);
        $this->admissionService = $this->createStub(AdmissionService::class);
        $this->admissionService->method('getRepository')->willReturn($this->admissionRepository);

        $this->ia = $this->createMock(InscriptionAdministrative::class);
    }

    protected function createRule(): ReceptionRule
    {
        $rule = new ReceptionRule();
        $rule->setDoctorantService($this->doctorantService);
        $rule->setTheseService($this->theseService);
        $rule->setAdmissionService($this->admissionService);

        return $rule;
    }
}