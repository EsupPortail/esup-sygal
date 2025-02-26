<?php

namespace InscriptionAdministrativeTest\Rule\Reception\StatutAnnulee;

use InscriptionAdministrative\Entity\Db\InscriptionAdministrative;
use InscriptionAdministrative\Entity\Db\InscriptionAdministrativeApprenant;
use InscriptionAdministrative\Rule\Reception\StatutAnnulee\StatutAnnuleeRule;
use InscriptionAdministrativeTest\Rule\Reception\ReceptionRuleAbstractTest;
use InvalidArgumentException;

class StatutAnnuleeRuleTest extends ReceptionRuleAbstractTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->iaApprenant = $this->createMock(InscriptionAdministrativeApprenant::class);

        $this->ia->method('estAnnulee')->willReturn(true);
        $this->ia->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);
    }

    public function test_throws_exception_with_inscription_non_annulee(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $ia = $this->createMock(InscriptionAdministrative::class);
        $ia->expects(static::once())->method('getInscriptionAdministrativeApprenant')->willReturn($this->iaApprenant);
        $ia->expects(static::once())->method('estAnnulee')->willReturn(false);

        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($ia);
        $rule->execute();
    }

    public function test_inscription_annulee(): void
    {
        $rule = $this->createRule();
        $rule->setInscriptionAdministrative($this->ia);
        $rule->execute();

        static::assertFalse(
            $rule->canPerformAnyAction(),
            "La règle ne devrait pas répondre qu'on peut réaliser la moindre action"
        );
    }

    protected function createRule(): StatutAnnuleeRule
    {
        $rule = new StatutAnnuleeRule();
        $rule->setDoctorantUpdateEnabled();

        return $rule;
    }
}
