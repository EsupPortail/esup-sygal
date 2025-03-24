<?php

namespace InscriptionAdministrativeTest\Process\Reception;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeApprenant;
use InscriptionAdministrative\Process\Reception\ReceptionProcess;
use InscriptionAdministrative\Rule\Reception\ReceptionRuleInterface;
use InscriptionAdministrative\Rule\Reception\ReceptionRulePluginManager;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReceptionProcessTest extends TestCase
{
    private InscriptionAdministrative|MockObject $ia;
    private ReceptionRulePluginManager|MockObject $rulePluginManager;

    protected function setUp(): void
    {
        $this->ia = $this->createMock(InscriptionAdministrative::class);
        $this->rulePluginManager = $this->createMock(ReceptionRulePluginManager::class);
    }

    public function test_throws_exception_when_setting_ia_having_no_apprenant()
    {
        $this->expectException(InvalidArgumentException::class);

        $process = new ReceptionProcess();
        $process->setInscriptionAdministrative($this->ia);
    }

    public function test_produces_sucess_result_when_nothing_is_done()
    {
        $this->ia->expects(self::once())->method('getInscriptionAdministrativeApprenant')->willReturn($this->createStub(InscriptionAdministrativeApprenant::class));

        $rule = $this->getMockBuilder(ReceptionRuleInterface::class)->getMock();
        $rule->method('canCreateDoctorant')->willReturn(false);
        $rule->method('canUpdateDoctorant')->willReturn(false);
        $rule->method('canUpdateCodeApprenantDoctorant')->willReturn(false);
        $rule->method('canCreateLinkInscriptionAdministrativeDoctorant')->willReturn(false);
        $rule->method('canCreateThese')->willReturn(false);
        $rule->method('canCreateTheseAnneeUniv')->willReturn(false);
        $rule->method('canLinkTheseAnneeUnivToInscriptionAdministrative')->willReturn(false);
        $rule->method('canLinkAdmissionToDoctorant')->willReturn(false);
        $this->rulePluginManager->method('getRuleForInscriptionAdministrative')->with($this->ia)->willReturn($rule);

        $em =  $this->createMock(EntityManager::class);
        $em->method('getConnection')->willReturn($this->createStub(Connection::class));

        $process = new ReceptionProcess();
        $process->setEntityManager($em);
        $process->setReceptionRulePluginManager($this->rulePluginManager);
        $process->setInscriptionAdministrative($this->ia);
        $process->run();

        $result = $process->getResult();
        self::assertTrue($result->isSuccess());
    }
}
