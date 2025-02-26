<?php

namespace InscriptionAdministrativeTest\Rule\Reception;

use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use InscriptionAdministrative\Rule\Reception\ReceptionRulePluginManager;
use InscriptionAdministrative\Rule\Reception\StatutAnnulee\StatutAnnuleeRule;
use InscriptionAdministrative\Rule\Reception\StatutAutre\StatutAutreRule;
use InscriptionAdministrative\Rule\Reception\StatutValidee\StatutValideeRule;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ReceptionRulePluginManagerTest extends TestCase
{
    protected ContainerInterface|MockObject $container;
    protected InscriptionAdministrative|MockObject $ia;

    protected function setUp(): void
    {
        $this->ia = $this->createMock(InscriptionAdministrative::class);
    }

    public function test_can_get_rule_for_inscription_administrative_annulee()
    {
        $this->ia->expects(self::once())->method('estAnnulee')->willReturn(true);

        $ruleManager = $this->getMockBuilder(ReceptionRulePluginManager::class)
            ->setConstructorArgs([$this->createStub(ContainerInterface::class)])
            ->onlyMethods(['get'])
            ->getMock();

        $rule = $this->createMock(StatutAnnuleeRule::class);
        $rule->expects(self::once())->method('setInscriptionAdministrative')->with($this->ia);

        $ruleManager->expects(self::once())->method('get')->with(StatutAnnuleeRule::class)->willReturn($rule);

        $ruleManager->getRuleForInscriptionAdministrative($this->ia);
    }

    public function test_can_get_rule_for_inscription_administrative_validee()
    {
        $this->ia->expects(self::once())->method('estValidee')->willReturn(true);

        $ruleManager = $this->getMockBuilder(ReceptionRulePluginManager::class)
            ->setConstructorArgs([$this->createStub(ContainerInterface::class)])
            ->onlyMethods(['get'])
            ->getMock();

        $rule = $this->createMock(StatutValideeRule::class);
        $rule->expects(self::once())->method('setInscriptionAdministrative')->with($this->ia);

        $ruleManager->expects(self::once())->method('get')->with(StatutValideeRule::class)->willReturn($rule);

        $ruleManager->getRuleForInscriptionAdministrative($this->ia);
    }

    public function test_can_get_rule_for_inscription_administrative_autre()
    {
        $this->ia->expects(self::once())->method('estAnnulee')->willReturn(false);
        $this->ia->expects(self::once())->method('estValidee')->willReturn(false);

        $ruleManager = $this->getMockBuilder(ReceptionRulePluginManager::class)
            ->setConstructorArgs([$this->createStub(ContainerInterface::class)])
            ->onlyMethods(['get'])
            ->getMock();

        $rule = $this->createMock(StatutAutreRule::class);
        $rule->expects(self::once())->method('setInscriptionAdministrative')->with($this->ia);

        $ruleManager->expects(self::once())->method('get')->with(StatutAutreRule::class)->willReturn($rule);

        $ruleManager->getRuleForInscriptionAdministrative($this->ia);
    }
}
